import moment from 'moment/moment';

export default {
    install: (app, options) => {
        app.config.globalProperties.$helpers = {

            formatDate(date, format = 'DD.MM.YYYY') {
                if(date && moment(date)) {
                    return moment(date).format(format);
                }
            },

            formatDateTime(date) {
                return this.formatDate(date);
            },

            formatDateRange(from, to, format = 'DD.MM.YYYY HH:mm') {

                if(moment(from).isSame(moment(to), 'hour')) {
                    return moment(from).format(format);
                }

                if(moment(from).isSame(moment(to), 'day') && format.endsWith('HH:mm')) {
                    return moment(from).format(format)+' - '+moment(to).format('HH:mm');
                }

                return moment(from).format(format)+' - '+moment(to).format(format);
            },

            formatCurrency(value, currency = 'CHF') {
                return parseFloat(value)
                  .toLocaleString('de-CH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })+' '+currency;
            },

            stripHTML(html) {
                let tmp = document.createElement('div');
                tmp.innerHTML = html;

                return (tmp.textContent || tmp.innerText || '').trim();
            },

            textExcerpt(text, length = 256, ellipsis = '...') {

                if(!text || text.length <= length - ellipsis.length) {
                    return text;
                }

                return text.slice(0, length - ellipsis.length).trim() + ellipsis;

            },

            sleep (ms = 1000) {
                return new Promise(resolve => setTimeout(resolve, ms));
            },

            translation: {

                field(field, label, options = {}) {
                    return {
                        type: 'field',
                        field: field,
                        label: label,
                        ...options,
                    };
                },

                collection(field, label, translateFields = [], options = {}) {
                    return {
                        type: 'collection',
                        field: field,
                        label: label,
                        translateFields: translateFields,
                        ...options,
                    };
                },

                clone(value) {
                    return JSON.parse(JSON.stringify(value));
                },

                getContext(context, locale, create = false) {

                    if(locale === 'de') {
                        return context;
                    }

                    if(!context.translations) {

                        if(!create) {
                            return {};
                        }

                        context.translations = {};
                    }

                    if(create && !context.translations[locale]) {
                        context.translations[locale] = {};
                    }

                    return context.translations[locale] || {};

                },

                getValue(context, field, locale) {

                    let translatedContext = this.getContext(context, locale);

                    return translatedContext[field] ?? '';

                },

                setValue(context, field, locale, value) {

                    let translatedContext = this.getContext(context, locale, true);

                    translatedContext[field] = value;

                },

                getCollection(context, field, locale) {

                    let translatedContext = this.getContext(context, locale);

                    return translatedContext[field] || [];

                },

                setCollection(context, field, locale, items) {

                    let translatedContext = this.getContext(context, locale, true);

                    translatedContext[field] = this.clone(items || []);

                },

                copyPart(context, sourceLocale, targetLocale, payload) {

                    if(payload.type === 'field') {

                        this.setValue(
                            context,
                            payload.field,
                            targetLocale,
                            this.getValue(context, payload.field, sourceLocale)
                        );

                        return;
                    }

                    if(payload.type === 'collection') {

                        this.setCollection(
                            context,
                            payload.field,
                            targetLocale,
                            this.getCollection(context, payload.field, sourceLocale)
                        );

                    }

                },

                buildFields(context, sourceLocale, payload) {

                    if(payload.type === 'field') {
                        return {
                            value: this.getValue(
                                context,
                                payload.field,
                                sourceLocale
                            ),
                        };
                    }

                    let fields = {};

                    if(payload.type === 'collection') {

                        this.getCollection(
                            context,
                            payload.field,
                            sourceLocale
                        ).forEach((item, index) => {

                            (payload.translateFields || []).forEach((field) => {

                                fields[
                                'item_'
                                + index
                                + '_'
                                + field
                                    ] = item[field] || '';

                            });

                        });

                    }

                    return fields;

                },

                applyTranslations(
                    context,
                    sourceLocale,
                    targetLocale,
                    payload,
                    translations
                ) {

                    if(payload.type === 'field') {

                        this.setValue(
                            context,
                            payload.field,
                            targetLocale,
                            translations.value || ''
                        );

                        return;
                    }

                    if(payload.type === 'collection') {

                        let items = this.clone(
                            this.getCollection(
                                context,
                                payload.field,
                                sourceLocale
                            )
                        );

                        items.forEach((item, index) => {

                            (payload.translateFields || []).forEach((field) => {

                                item[field] = translations[
                                'item_'
                                + index
                                + '_'
                                + field
                                    ] || '';

                            });

                        });

                        this.setCollection(
                            context,
                            payload.field,
                            targetLocale,
                            items
                        );

                    }

                },

                copyParts(
                    context,
                    sourceLocale,
                    targetLocale,
                    parts
                ) {

                    Object.values(parts || {}).forEach((payload) => {

                        if(payload.canCopy === false) {
                            return;
                        }

                        this.copyPart(
                            context,
                            sourceLocale,
                            targetLocale,
                            payload
                        );

                    });

                },

                buildPartsFields(
                    context,
                    sourceLocale,
                    parts,
                    prefix = ''
                ) {

                    let fields = {};

                    Object.values(parts || {}).forEach((payload) => {

                        if(payload.canTranslate === false) {
                            return;
                        }

                        let translationKey = payload.translationKey !== undefined
                            ? payload.translationKey
                            : payload.field;

                        if(payload.type === 'field') {

                            fields[
                            prefix
                            + translationKey
                                ] = this.getValue(
                                context,
                                payload.field,
                                sourceLocale
                            );

                            return;
                        }

                        if(payload.type === 'collection') {

                            this.getCollection(
                                context,
                                payload.field,
                                sourceLocale
                            ).forEach((item, index) => {

                                (payload.translateFields || []).forEach((field) => {

                                    fields[
                                    prefix
                                    + translationKey
                                    + '_'
                                    + index
                                    + '_'
                                    + field
                                        ] = item[field] || '';

                                });

                            });

                        }

                    });

                    return fields;

                },

                applyPartsTranslations(
                    context,
                    sourceLocale,
                    targetLocale,
                    parts,
                    translations,
                    prefix = ''
                ) {

                    Object.values(parts || {}).forEach((payload) => {

                        if(payload.canTranslate === false) {

                            if(payload.copyOnTranslate) {

                                this.copyPart(
                                    context,
                                    sourceLocale,
                                    targetLocale,
                                    payload
                                );

                            }

                            return;
                        }

                        let translationKey = payload.translationKey !== undefined
                            ? payload.translationKey
                            : payload.field;

                        if(payload.type === 'field') {

                            this.setValue(
                                context,
                                payload.field,
                                targetLocale,
                                translations[
                                prefix
                                + translationKey
                                    ] || ''
                            );

                            return;
                        }

                        if(payload.type === 'collection') {

                            let items = this.clone(
                                this.getCollection(
                                    context,
                                    payload.field,
                                    sourceLocale
                                )
                            );

                            items.forEach((item, index) => {

                                (payload.translateFields || []).forEach((field) => {

                                    item[field] = translations[
                                    prefix
                                    + translationKey
                                    + '_'
                                    + index
                                    + '_'
                                    + field
                                        ] || '';

                                });

                            });

                            this.setCollection(
                                context,
                                payload.field,
                                targetLocale,
                                items
                            );

                        }

                    });

                },

            },

        };
    }
}
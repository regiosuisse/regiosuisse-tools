<template>

    <div class="embed-contacts-view" :class="$env.INSTANCE_ID+'-contacts-view'">

        <template v-if="!contact.officialEmployment || officialEmployment?.id">

            <div class="embed-contacts-view-header">

                <h1 class="embed-contacts-view-header-title">{{ contact.academicTitle }} {{ translateField(contact, 'name', locale) }}</h1>
                <p v-if="contact.type === 'person' && officialEmployment?.role"><i>{{ translateField(officialEmployment, 'role', locale) }}</i></p>
                <p v-if="contact.type === 'company' && contact.specification"><i>{{ translateField(contact, 'specification', locale) }}</i></p>

                <div class="embed-contacts-view-links" v-if="contact.type === 'person'&& officialEmployment?.id && officialEmployment?.company?.id">

                    <template v-if="contact.phone">
                        {{ $t('Tel.', locale) }}: <a :href="'tel:'+contact.phone">{{ contact.phone }}</a><br>
                    </template>

                    <div class="embed-contacts-view-links-icons">
                        <a v-if="contact.email" class="contact-link" :href="'mailto:'+contact.email" target="_blank">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="11.5" viewBox="0 0 14 11.5">
                                <path id="Subtract" d="M0,2.75a1.5,1.5,0,0,1,1.5-1.5h11A1.5,1.5,0,0,1,14,2.75v.342L7.383,7.5a.764.764,0,0,1-.766,0L0,3.092ZM0,4.594V11.25a1.5,1.5,0,0,0,1.5,1.5h11a1.5,1.5,0,0,0,1.5-1.5V4.594L8.073,8.546l-.005,0a2.011,2.011,0,0,1-2.136,0l-.005,0Z" transform="translate(0 -1.25)" fill="var(--regiosuisse-primary-color)" fill-rule="evenodd"/>
                            </svg>
                        </a>
                        <a v-if="contact.linkedIn" class="contact-link" :href="contact.linkedIn" target="_blank">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16.426" height="16.426" viewBox="0 0 16.426 16.426">
                                <path id="Linkedin-Logo--Streamline-Logos-Block" d="M3.986,1A2.986,2.986,0,0,0,1,3.986V14.439a2.986,2.986,0,0,0,2.986,2.986H14.439a2.986,2.986,0,0,0,2.986-2.986V3.986A2.986,2.986,0,0,0,14.439,1Zm.9,5.159A1.273,1.273,0,1,0,3.613,4.886,1.273,1.273,0,0,0,4.886,6.159Zm1.272,8.653V7.177H3.613v7.636H6.159ZM9.467,7.177H7.177v7.636H9.467V10.206a1.858,1.858,0,0,1,1.527-.993c1.018,0,1.273,1.018,1.273,1.528v4.072h2.545V10.74c0-1.365-.6-3.564-2.8-3.564a3.05,3.05,0,0,0-2.546.991V7.177Z" transform="translate(-1 -1)" fill="var(--regiosuisse-primary-color)" fill-rule="evenodd"/>
                            </svg>
                        </a>
                    </div>

                </div>

                <a class="embed-contacts-view-header-close" @click="clickClose()"></a>

            </div>

            <div class="embed-contacts-view-content">

                <div class="embed-contacts-view-content-text" v-if="contact.description">
                    <div class="embed-contacts-view-content-description" v-html="translateField(contact, 'description', locale)"></div><br>
                </div>

                <!--div class="embed-contacts-view-content-text" v-if="topicsHTML">
                    <strong>{{ $t('Thema', locale) }}:</strong><br>
                    <div class="embed-contacts-view-content-description" v-html="topicsHTML"></div><br>
                </div-->

                <div class="embed-contacts-view-content-text">

                    <template v-if="contact.type === 'person'&& officialEmployment?.id && officialEmployment?.company?.id">

                        <p>
                            <strong>{{ translateField(officialEmployment.company, 'companyName', locale) }}</strong><br>
                            <template v-if="officialEmployment.company.specification">
                                {{ translateField(officialEmployment.company, 'specification', locale) }}<br>
                            </template>
                            <br>
                            <template v-if="officialEmployment.company.street">
                                {{ officialEmployment.company.street }}<br>
                            </template>
                            <template v-if="officialEmployment.company.city || officialEmployment.company.state">
                                {{ officialEmployment.company.zipCode }} {{ translateField(officialEmployment.company, 'city', locale) }} {{ officialEmployment.company.state ? '('+translateField(getStateById(officialEmployment.company.state.id), 'name', locale)+')' : '' }}<br>
                            </template>
                            <template v-if="officialEmployment.company.country">
                                {{ translateField(officialEmployment.company.country, 'name', locale) || '' }}<br>
                            </template>
                        </p>

                        <div class="embed-contacts-view-links">

                            {{ $t('Tel.', locale) }}: <a :href="'tel:'+officialEmployment.company.phone">{{ officialEmployment.company.phone }}</a><br>

                            <div class="embed-contacts-view-links-icons">
                                <a v-if="officialEmployment.company.email" class="contact-link" :href="'mailto:'+officialEmployment.company.email" target="_blank">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="11.5" viewBox="0 0 14 11.5">
                                        <path id="Subtract" d="M0,2.75a1.5,1.5,0,0,1,1.5-1.5h11A1.5,1.5,0,0,1,14,2.75v.342L7.383,7.5a.764.764,0,0,1-.766,0L0,3.092ZM0,4.594V11.25a1.5,1.5,0,0,0,1.5,1.5h11a1.5,1.5,0,0,0,1.5-1.5V4.594L8.073,8.546l-.005,0a2.011,2.011,0,0,1-2.136,0l-.005,0Z" transform="translate(0 -1.25)" fill="var(--regiosuisse-primary-color)" fill-rule="evenodd"/>
                                    </svg>
                                </a>
                                <a v-if="officialEmployment.company.linkedIn" class="contact-link" :href="officialEmployment.company.linkedIn" target="_blank">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16.426" height="16.426" viewBox="0 0 16.426 16.426">
                                        <path id="Linkedin-Logo--Streamline-Logos-Block" d="M3.986,1A2.986,2.986,0,0,0,1,3.986V14.439a2.986,2.986,0,0,0,2.986,2.986H14.439a2.986,2.986,0,0,0,2.986-2.986V3.986A2.986,2.986,0,0,0,14.439,1Zm.9,5.159A1.273,1.273,0,1,0,3.613,4.886,1.273,1.273,0,0,0,4.886,6.159Zm1.272,8.653V7.177H3.613v7.636H6.159ZM9.467,7.177H7.177v7.636H9.467V10.206a1.858,1.858,0,0,1,1.527-.993c1.018,0,1.273,1.018,1.273,1.528v4.072h2.545V10.74c0-1.365-.6-3.564-2.8-3.564a3.05,3.05,0,0,0-2.546.991V7.177Z" transform="translate(-1 -1)" fill="var(--regiosuisse-primary-color)" fill-rule="evenodd"/>
                                    </svg>
                                </a>
                                <a v-if="officialEmployment.company.website" class="contact-link" :href="translateField(officialEmployment.company, 'website', locale)" target="_blank">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13.945" height="14" viewBox="0 0 13.945 14">
                                        <path id="Subtract" d="M5.049.276a7.006,7.006,0,0,0-5.021,6.1h3.2A15.763,15.763,0,0,1,5.049.276ZM3.228,7.625H.028a7.006,7.006,0,0,0,5.021,6.1A15.763,15.763,0,0,1,3.228,7.625Zm3.424,6.366A14.477,14.477,0,0,1,4.483,7.625H9.517a14.477,14.477,0,0,1-2.169,6.366Q7.175,14,7,14t-.348-.009Zm2.3-.267a7.006,7.006,0,0,0,5.021-6.1h-3.2A15.763,15.763,0,0,1,8.951,13.724Zm1.821-7.349h3.2A7.006,7.006,0,0,0,8.951.276,15.762,15.762,0,0,1,10.772,6.375ZM6.652.008Q6.825,0,7,0t.348.008A14.477,14.477,0,0,1,9.517,6.375H4.483A14.477,14.477,0,0,1,6.652.008Z" transform="translate(-0.028)" fill="var(--regiosuisse-primary-color)" fill-rule="evenodd"/>
                                    </svg>
                                </a>
                            </div>

                        </div>

                    </template>

                    <template v-else>
                        <p v-if="contact.street || contact.zipCode || contact.city">
                            <template v-if="contact.street">
                                {{ contact.street }}<br>
                            </template>
                            <template v-if="contact.street || contact.state">
                                {{ contact.zipCode }} {{  translateField(contact, 'city', locale) }} {{ contact.state ? '('+translateField(contact.state, 'name', locale)+')' : '' }}<br>
                            </template>
                            <template v-if="contact.country">
                                {{  translateField(contact.country, 'name', locale) }}<br>
                            </template>
                        </p>

                        <div class="embed-contacts-view-links">

                            <template v-if="contact.phone">
                                {{ $t('Tel.', locale) }}: <a :href="'tel:'+contact.phone">{{ contact.phone }}</a><br>
                            </template>

                            <div class="embed-contacts-view-links-icons">
                                <a v-if="contact.email" class="contact-link" :href="'mailto:'+contact.email" target="_blank">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="11.5" viewBox="0 0 14 11.5">
                                        <path id="Subtract" d="M0,2.75a1.5,1.5,0,0,1,1.5-1.5h11A1.5,1.5,0,0,1,14,2.75v.342L7.383,7.5a.764.764,0,0,1-.766,0L0,3.092ZM0,4.594V11.25a1.5,1.5,0,0,0,1.5,1.5h11a1.5,1.5,0,0,0,1.5-1.5V4.594L8.073,8.546l-.005,0a2.011,2.011,0,0,1-2.136,0l-.005,0Z" transform="translate(0 -1.25)" fill="var(--regiosuisse-primary-color)" fill-rule="evenodd"/>
                                    </svg>
                                </a>
                                <a v-if="contact.linkedIn" class="contact-link" :href="contact.linkedIn" target="_blank">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16.426" height="16.426" viewBox="0 0 16.426 16.426">
                                        <path id="Linkedin-Logo--Streamline-Logos-Block" d="M3.986,1A2.986,2.986,0,0,0,1,3.986V14.439a2.986,2.986,0,0,0,2.986,2.986H14.439a2.986,2.986,0,0,0,2.986-2.986V3.986A2.986,2.986,0,0,0,14.439,1Zm.9,5.159A1.273,1.273,0,1,0,3.613,4.886,1.273,1.273,0,0,0,4.886,6.159Zm1.272,8.653V7.177H3.613v7.636H6.159ZM9.467,7.177H7.177v7.636H9.467V10.206a1.858,1.858,0,0,1,1.527-.993c1.018,0,1.273,1.018,1.273,1.528v4.072h2.545V10.74c0-1.365-.6-3.564-2.8-3.564a3.05,3.05,0,0,0-2.546.991V7.177Z" transform="translate(-1 -1)" fill="var(--regiosuisse-primary-color)" fill-rule="evenodd"/>
                                    </svg>
                                </a>
                                <a v-if="contact.website" class="contact-link" :href="translateField(contact, 'website', locale)" target="_blank">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13.945" height="14" viewBox="0 0 13.945 14">
                                        <path id="Subtract" d="M5.049.276a7.006,7.006,0,0,0-5.021,6.1h3.2A15.763,15.763,0,0,1,5.049.276ZM3.228,7.625H.028a7.006,7.006,0,0,0,5.021,6.1A15.763,15.763,0,0,1,3.228,7.625Zm3.424,6.366A14.477,14.477,0,0,1,4.483,7.625H9.517a14.477,14.477,0,0,1-2.169,6.366Q7.175,14,7,14t-.348-.009Zm2.3-.267a7.006,7.006,0,0,0,5.021-6.1h-3.2A15.763,15.763,0,0,1,8.951,13.724Zm1.821-7.349h3.2A7.006,7.006,0,0,0,8.951.276,15.762,15.762,0,0,1,10.772,6.375ZM6.652.008Q6.825,0,7,0t.348.008A14.477,14.477,0,0,1,9.517,6.375H4.483A14.477,14.477,0,0,1,6.652.008Z" transform="translate(-0.028)" fill="var(--regiosuisse-primary-color)" fill-rule="evenodd"/>
                                    </svg>
                                </a>
                            </div>

                        </div>

                    </template>

                </div>

                <div class="embed-contacts-view-content-text">

                    <button type="button" class="embed-contacts-button" @click="clickDownloadVCard">{{ $t('Als Kontakt speichern', locale) }}</button>

                </div>

            </div>

            <div class="embed-contacts-view-sidebar"></div>

        </template>

    </div>

</template>

<script>

import {mapGetters, mapState} from 'vuex';
import { translateField } from '../utils/filters';

export default {

    data() {
        return {
            lightboxImage: null,
        };
    },

    props: {
        locale: {
            type: String,
            default: 'de',
            required: false,
        },
        contact: {
            type: Object,
            required: true,
        },
        officialEmployment: {
            type: Object,
            required: false,
            default: null,
        },
    },

    emits: [
        'clickClose',
    ],

    computed: {
        ...mapState({
            states: state => state.states.all,
            topics: state => state.topics.all,
            employments: state => state.employments.all,
        }),
        ...mapGetters({
            getStateById: 'states/getById',
            getCountryById: 'countries/getById',
            getContactById: 'contacts/getById',
            getEmploymentById: 'employments/getById',
            getLanguageById: 'languages/getById',
            getTopicById: 'topics/getById',
        }),
        contactHTML () {

            let result = translateField(this.contact, 'contact', this.locale).split('\n');

            for(let contactRowKey in result) {
                result[contactRowKey] = result[contactRowKey]
                    .replace(/([a-z0-9._-]+@[a-z0-9.-]+\.[a-z]{2,4})/ig, '<a href="mailto:$1">$1</a>');
            }

            for(let contactRowKey in result) {
                result[contactRowKey] = result[contactRowKey]
                    .replace(/((http:|https:)[^\s]+[\w])/g, '<a href="$1" target="_blank">Website</a>');
            }

            return result.join('<br>');
        },
        linksHTML () {
            let result = [];

            translateField(this.contact, 'links', this.locale).forEach((item) => {

                let url = item.value.split('://').length > 1 ? item.value : 'http://'+item.value;

                let row = '<a href="'+url+'" target="_blank">'+item.label+'</a>';

                result.push(row);

            });

            return result.join('<br>');
        },
        topicsHTML () {
            let result = [];

            this.contact.topics.forEach((item) => {

                let row = this.translateField(this.getTopicById(item.id), 'name', this.locale);

                result.push(row);

            });

            return result.join(', ');
        },
    },

    methods: {
        translateField,

        clickClose() {
            this.$emit('clickClose');
        },

        clickDownloadVCard() {
            const c = this.contact;
            const comp = this.officialEmployment?.company || null;

            const escapeVCard = (val = '') => String(val ?? '')
                .replace(/\\/g, '\\\\')
                .replace(/\n/g, '\\n')
                .replace(/,/g, '\\,')
                .replace(/;/g, '\\;');

            const parseHtmlToVCardLines = (html = '') => {
                let text = String(html ?? '')
                    .replace(/<\/p>/g, '\n')
                    .replace(/<\/div>/g, '\n')
                    .replace(/<br\s*\/?>/g, '\n')
                    .replace(/<\/li>/g, ' \n');

                text = text.replace(/<[^>]*>/g, '').trim();
                return text.split('\n').map(line => line.trim()).join('\\n');
            };

            const getTranslation = (obj, field) =>
                obj ? (this.translateField(obj, field, this.locale) || '') : '';

            const getCountryName = (countryObj) =>
                countryObj
                    ? getTranslation(this.getCountryById(countryObj.id) || countryObj, 'name')
                    : '';

            const companyName = comp
                ? getTranslation(comp, 'companyName')
                : (c.type === 'company' ? getTranslation(c, 'name') : '');

            let firstName = c.firstName || '';
            let lastName = c.lastName || '';
            let academicTitle = c.academicTitle || '';

            let fullName = '';
            let lastNameField = lastName;
            let firstNameField = firstName;

            if (!firstName && !lastName && companyName) {
                fullName = companyName;
                lastNameField = companyName;
                firstNameField = '';
                academicTitle = '';
            } else {
                fullName = [academicTitle, firstName, lastName].filter(Boolean).join(' ');
            }

            const usePersonAddress = !!(c.street || c.zipCode || getTranslation(c, 'city') || c.state);

            let street = '';
            let zip = '';
            let city = '';
            let state = '';
            let country = '';

            if (usePersonAddress) {
                street = c.street || '';
                zip = c.zipCode || '';
                city = getTranslation(c, 'city');

                const stateObj = c.state || null;
                state = stateObj
                    ? getTranslation(stateObj.id ? this.getStateById(stateObj.id) : stateObj, 'name')
                    : '';

                country = getCountryName(c.country || null);
            } else if (comp) {
                street = comp.street || '';
                zip = comp.zipCode || '';
                city = getTranslation(comp, 'city');

                const stateObj = comp.state || null;
                state = stateObj
                    ? getTranslation(stateObj.id ? this.getStateById(stateObj.id) : stateObj, 'name')
                    : '';

                country = getCountryName(comp.country || null);
            } else {
                street = c.street || '';
                zip = c.zipCode || '';
                city = getTranslation(c, 'city');

                const stateObj = c.state || null;
                state = stateObj
                    ? getTranslation(stateObj.id ? this.getStateById(stateObj.id) : stateObj, 'name')
                    : '';

                country = getCountryName(c.country || null);
            }

            let streetCombined = street;
            const zipCity = [zip, city].filter(Boolean).join(' ');

            if (zipCity) streetCombined += `\n${zipCity}`;
            if (state) streetCombined += `\n${state}`;
            if (country) streetCombined += `\n${country}`;

            const vanishedStreet = escapeVCard(streetCombined);
            const adrFields = `;;${vanishedStreet};;;;`;

            const email = c.email || comp?.email || '';
            const phone = c.phone || comp?.phone || '';

            const website =
                getTranslation(c, 'website') ||
                getTranslation(comp, 'website') ||
                '';

            const linkedIn = c.linkedIn || comp?.linkedIn || '';

            const rawDescription = getTranslation(c, 'description');
            const descriptionText = parseHtmlToVCardLines(rawDescription);

            const vcardLines = [
                'BEGIN:VCARD',
                'VERSION:3.0',
                `FN:${escapeVCard(fullName)}`,
                `N:${escapeVCard(lastNameField)};${escapeVCard(firstNameField)};;${escapeVCard(academicTitle)};`,
                companyName ? `ORG:${escapeVCard(companyName)}` : '',
                this.officialEmployment?.role
                    ? `TITLE:${escapeVCard(getTranslation(this.officialEmployment, 'role'))}`
                    : '',
                email ? `EMAIL;TYPE=INTERNET:${escapeVCard(email)}` : '',
                phone ? `TEL:${escapeVCard(phone)}` : '',
                streetCombined ? `ADR:${adrFields}` : '',
                website ? `URL:${escapeVCard(website)}` : '',
                linkedIn ? `URL:${escapeVCard(linkedIn)}` : '',
                descriptionText ? `NOTE:${descriptionText}` : '',
                `REV:${new Date().toISOString()}`,
                'END:VCARD'
            ];

            const vcard = vcardLines.filter(Boolean).join('\r\n');
            const blob = new Blob([vcard], { type: 'text/vcard;charset=utf-8' });
            const url = URL.createObjectURL(blob);

            const link = document.createElement('a');
            link.href = url;
            link.download = `${fullName || 'contact'}.vcf`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        },

    },

};

</script>
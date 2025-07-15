<template>

    <div class="publication-component">

        <div class="publication-component-form">

            <div class="publication-component-form-header">

                <h3>Eintrag erstellen</h3>

                <div class="publication-component-form-header-actions">
                    <a class="button warning" @click="publication.isPublic = true" v-if="!publication.isPublic">Entwurf</a>
                    <a class="button success" @click="publication.isPublic = false" v-if="publication.isPublic">Öffentlich</a>
                    <a @click="locale = 'de'" class="button" :class="{primary: locale === 'de'}">DE</a>
                    <a @click="locale = 'fr'" class="button" :class="{primary: locale === 'fr'}">FR</a>
                    <a @click="locale = 'it'" class="button" :class="{primary: locale === 'it'}">IT</a>
                    <a @click="locale = 'en'" class="button" :class="{primary: locale === 'en'}">EN</a>
                    <a class="button error" @click="clickDelete()" v-if="publication.id">Löschen</a>
                    <a class="button warning" @click="clickCancel()">Abbrechen</a>
                    <a class="button primary" @click="clickSave()">Speichern</a>
                </div>

            </div>

            <div class="publication-component-form-section">

                <div class="row">
                    <div class="col-md-6" v-if="locale === 'de'">
                        <label for="title">Bezeichnung</label>
                        <input id="title" type="text" class="form-control" v-model="publication.title" :placeholder="translate('title', publication)">
                    </div>
                    <div class="col-md-6" v-else>
                        <label for="title">Bezeichnung (Übersetzung {{ locale.toUpperCase() }})</label>
                        <input id="title" type="text" class="form-control" v-model="publication.translations[locale].title" :placeholder="translate('title', publication)">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8" v-if="locale === 'de'">
                        <label for="description">Kurzbeschrieb</label>
                        <textarea name="description" id="description" class="form-control" rows="4" v-model="publication.description"></textarea>
                    </div>
                    <div class="col-md-8" v-else>
                        <label for="description">Kurzbeschrieb (Übersetzung {{ locale.toUpperCase() }})</label>
                        <textarea name="description" id="description" class="form-control" rows="4" v-model="publication.translations[locale].description"></textarea>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8" v-if="locale === 'de'">
                        <label for="keywords">Keywords</label>
                        <textarea name="keywords" id="keywords" class="form-control" rows="1" v-model="publication.keywords"></textarea>
                    </div>
                    <div class="col-md-8" v-else>
                        <label for="keywords">Keywords (Übersetzung {{ locale.toUpperCase() }})</label>
                        <textarea name="keywords" id="keywords" class="form-control" rows="1" v-model="publication.translations[locale].keywords"></textarea>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label for="email">E-Mail für Rückfragen</label>
                        <input id="email" type="text" class="form-control" v-model="publication.email">
                    </div>
                </div>

                <div class="publication-component-form-section-group">

                    <div class="publication-component-form-section-group-headline">Publikation</div>

                    <div class="row">
                        <div class="col-md-8" v-if="locale === 'de'">
                            <label for="url">Publikations-URL</label>
                            <input id="url" type="text" class="form-control" v-model="publication.url" :placeholder="translate('url', publication)">
                        </div>
                        <div class="col-md-8" v-else>
                            <label for="url">Publikations-URL (Übersetzung {{ locale.toUpperCase() }})</label>
                            <input id="url" type="text" class="form-control" v-model="publication.translations[locale].url" :placeholder="translate('url', publication)">
                        </div>
                        <div class="col-md-4">
                            <label for="type">Typ</label>
                            <div class="select-wrapper">
                                <select id="licenseType" class="form-control" v-model="publication.type">
                                    <option :value="'publication'">Publikation</option>
                                    <option :value="'project'">Projekt</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <label for="startDate" v-if="publication.endDate !== publication.startDate">Startdatum</label>
                            <label for="startDate" v-else>Datum</label>
                            <date-picker mode="date"
                                         :is24hr="true"
                                         v-model="publication.startDate"
                                         @update:modelValue="publication.endDate = publication.startDate"
                                         :locale="'de'">
                                <template v-slot="{ inputValue, inputEvents }">
                                    <input type="text" class="form-control"
                                           :value="inputValue"
                                           v-on="inputEvents"
                                           id="startDate">
                                </template>
                            </date-picker>
                        </div>
                        <div class="col-md-4" v-if="publication.endDate !== publication.startDate">
                            <label for="endDate">Enddatum</label>
                            <date-picker mode="date"
                                         :is24hr="true"
                                         v-model="publication.endDate"
                                         :locale="'de'">
                                <template v-slot="{ inputValue, inputEvents }">
                                    <input type="text" class="form-control"
                                           :value="inputValue"
                                           v-on="inputEvents"
                                           id="endDate">
                                </template>
                            </date-picker>
                        </div>
                        <template v-if="publication.startDate">
                            <div class="col-md-4" v-if="publication.endDate !== publication.startDate">
                                <label>Zeitraum</label>
                                <span @click="publication.endDate = publication.startDate">
                                    <input type="checkbox" checked>
                                    Zeitraum angeben
                                </span>
                            </div>
                            <div class="col-md-4" v-else>
                                <label>Zeitraum</label>
                                <span @click="publication.endDate = null">
                                    <input type="checkbox">
                                    Zeitraum angeben
                                </span>
                            </div>
                        </template>
                    </div>

                </div>

                <div class="publication-component-form-section-group">

                    <div class="publication-component-form-section-group-headline">Lizenz</div>

                    <div class="row">
                        <div class="col-md-4">
                            <label for="licenseType">Lizenz</label>
                            <div class="select-wrapper">
                                <select id="licenseType" class="form-control" v-model="publication.licenseType">
                                    <option v-for="licenseType in licenseTypes" :value="licenseType.type">{{ licenseType.name }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="rightsHolder">Rechte-Inhaber</label>
                            <input id="rightsHolder" type="text" class="form-control" v-model="publication.rightsHolder" :placeholder="translate('rightsHolder', publication)">
                        </div>
                    </div>

                    <div class="row" v-if="publication.licenseType === 'Other'">
                        <div class="col-md-4" v-if="locale === 'de'">
                            <label for="licenseName">Lizenz-Bezeichnung</label>
                            <input id="licenseName" type="text" class="form-control" v-model="publication.licenseName" :placeholder="translate('licenseName', publication)">
                        </div>
                        <div class="col-md-4" v-else>
                            <label for="licenseName">Lizenz-Bezeichnung (Übersetzung {{ locale.toUpperCase() }})</label>
                            <input id="licenseName" type="text" class="form-control" v-model="publication.translations[locale].licenseName" :placeholder="translate('licenseName', publication)">
                        </div>
                        <div class="col-md-4" v-if="locale === 'de'">
                            <label for="licenseUrl">Lizenz-URL</label>
                            <input id="licenseUrl" type="text" class="form-control" v-model="publication.licenseUrl" :placeholder="translate('licenseUrl', publication)">
                        </div>
                        <div class="col-md-4" v-else>
                            <label for="licenseUrl">Lizenz-URL (Übersetzung {{ locale.toUpperCase() }})</label>
                            <input id="licenseUrl" type="text" class="form-control" v-model="publication.translations[locale].licenseUrl" :placeholder="translate('licenseUrl', publication)">
                        </div>
                        <div class="col-md-4" v-if="locale === 'de'">
                            <label for="licenseAttribution">Lizenz-Attribution</label>
                            <input id="licenseAttribution" type="text" class="form-control" v-model="publication.licenseAttribution" :placeholder="translate('licenseAttribution', publication)">
                        </div>
                        <div class="col-md-4" v-else>
                            <label for="licenseAttribution">Lizenz-Attribution (Übersetzung {{ locale.toUpperCase() }})</label>
                            <input id="licenseAttribution" type="text" class="form-control" v-model="publication.translations[locale].licenseAttribution" :placeholder="translate('licenseAttribution', publication)">
                        </div>
                    </div>

                </div>

                <div class="publication-component-form-section-group">

                    <div class="publication-component-form-section-group-headline">Kategorisierung</div>

                    <div class="row">
                        <div class="col-md-4">
                            <label for="languages">Sprache</label>
                            <tag-selector id="languages" :model="publication.languages"
                                          :options="languages.filter(language => !language.context || language.context === 'publication')" :searchType="'select'"></tag-selector>
                        </div>
                        <div class="col-md-4">
                            <label for="geographicRegions">Geographische Region</label>
                            <tag-selector id="geographicRegions" :model="publication.geographicRegions"
                                          :options="geographicRegions.filter(geographicRegion => !geographicRegion.context || geographicRegion.context === 'publication')" :searchType="'select'"></tag-selector>
                        </div>
                        <div class="col-md-4">
                            <label for="topics">Thema</label>
                            <tag-selector id="topics" :model="publication.topics"
                                          :options="topics.filter(topic => !topic.context || topic.context === 'publication')" :searchType="'select'"></tag-selector>
                        </div>
                    </div>

                </div>

                <div class="publication-component-form-section-group">

                    <div class="publication-component-form-section-group-headline">Urheber</div>

                    <div class="row">
                        <div class="col-md-6">
                            <label>Autor</label>
                            <div class="row" v-for="(e, index) in publication.authors">
                                <div class="col-md-8">
                                    <input type="text" class="form-control" v-model="e.name" placeholder="Name">
                                </div>
                                <div class="col-md-4">
                                    <button class="button error" @click="publication.authors.splice(index, 1)">Autor entfernen</button>
                                </div>
                            </div>
                            <br>
                            <button class="button success" @click="publication.authors.push({})">Autor hinzufügen</button>
                        </div>
                        <div class="col-md-6">
                            <label>Organisation / Institution</label>
                            <div class="row" v-for="(e, index) in publication.organizations">
                                <div class="col-md-8">
                                    <input type="text" class="form-control" v-model="e.name" placeholder="Name">
                                </div>
                                <div class="col-md-4">
                                    <button class="button error" @click="publication.organizations.splice(index, 1)">Organisation entfernen</button>
                                </div>
                            </div>
                            <br>
                            <button class="button success" @click="publication.organizations.push({})">Organisation hinzufügen</button>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        <transition name="fade">
            <Modal v-if="modal" :config="modal"></Modal>
        </transition>

    </div>

</template>

<script>
import { mapState } from 'vuex';
import draggable from 'vuedraggable';
import TagSelector from './TagSelector';
import FileSelector from './FileSelector';
import { DatePicker } from 'v-calendar';
import Modal from './Modal';
import {licenseTypes} from '../common/licenseTypes';

export default {
    data() {
        return {
            licenseTypes,
            locale: 'de',
            publication: {
                isPublic: false,
                title: '',
                keywords: '',
                description: '',
                type: 'publication',
                url: null,
                startDate: null,
                endDate: null,
                licenseType: 'Other',
                licenseName: null,
                licenseUrl: null,
                licenseAttribution: null,
                rightsHolder: null,
                topics: [],
                languages: [],
                geographicRegions: [],
                authors: [],
                organizations: [],
                translations: {
                    fr: {},
                    it: {},
                    en: {},
                },
            },
            inboxId: null,
            showPreview: false,
            modal: null,
        };
    },
    components: {
        TagSelector,
        FileSelector,
        DatePicker,
        draggable,
        Modal,
    },
    computed: {
        ...mapState({
            inboxItem: state => state.inbox.item,
            topics: state => state.topics.all,
            languages: state => state.languages.all,
            geographicRegions: state => state.geographicRegions.all,
            selectedPublication: state => state.publications.publication,
        }),
    },
    methods: {
        clickDelete () {
            this.modal = {
                title: 'Eintrag löschen',
                description: 'Sind Sie sicher dass Sie diesen Eintrag unwiderruflich löschen möchten?',
                actions: [
                    {
                        label: 'Endgültig löschen',
                        class: 'error',
                        onClick: () => {
                            this.$store.dispatch('publications/delete', this.publication.id).then(() => {
                                this.$router.push('/publications');
                            });
                        }
                    },
                    {
                        label: 'Abbrechen',
                        class: 'warning',
                        onClick: () => {
                            this.modal = null;
                        }
                    }
                ],
            };
        },
        clickCancel () {
            this.$router.push('/publications');
        },
        async clickSave() {
            try {

                let response;
                if (this.publication.id) {
                    // If publication has an ID, update it
                    response = await this.$store.dispatch('publications/update', {
                        id: this.publication.id,
                        payload: this.publication,
                    });
                } else {
                    // If no ID, create new publication
                    response = await this.$store.dispatch('publications/create', this.publication);
                }
                
                // If this was from an inbox item, delete it after successful save
                if (this.inboxId) {
                    await this.$store.dispatch('inbox/delete', this.inboxId);
                }
                
                this.$router.push('/publications');
            } catch (error) {
                console.error('Error saving publication:', error);
                this.modal = {
                    type: 'error',
                    title: 'Fehler beim Speichern',
                    message: error.response?.data?.error || 'Fehler beim Speichern der Publikation.',
                    actions: [
                        {
                            label: 'Abbrechen',
                            class: 'warning',
                            onClick: () => {
                                this.modal = null;
                            }
                        }
                    ]
                };
            }
        },
        async reload() {
            if (this.$route.params.id) {
                // Only load existing publication if we have an ID
                this.$store.commit('publications/set', {});
                await this.$store.dispatch('publications/load', this.$route.params.id);
                if (this.selectedPublication) {
                    // Ensure translations object exists
                    const translations = this.selectedPublication.translations || {
                        fr: { links: [] },
                        it: { links: [] }
                    };
                    
                    this.publication = {
                        ...this.publication, // Keep default structure
                        ...this.selectedPublication,
                        translations: {
                            fr: { ...this.publication.translations.fr, ...translations.fr },
                            it: { ...this.publication.translations.it, ...translations.it }
                        }
                    };
                }
            } else if (this.inboxId) {
                await this.loadInboxPublication();
            }
        },
        translate(property, context) {
            if (!context || !context.translations) {
                return context?.[property] || '';
            }

            if(this.locale === 'de') {
                return context[property] || context.translations?.fr?.[property] || context.translations?.it?.[property] || '';
            }
            if(this.locale === 'fr') {
                return context.translations?.fr?.[property] || context[property] || context.translations?.it?.[property] || '';
            }
            if(this.locale === 'it') {
                return context.translations?.it?.[property] || context.translations?.fr?.[property] || context[property] || '';
            }
            return context[property] || '';
        },
        getPreviewLink() {
            let url = 'https://regiosuisse.ch';

            if(this.locale === 'fr') {
                url += '/fr';
            }

            if(this.locale === 'it') {
                url += '/it';
            }

            return url + '/agenda?publication-id=' + this.publication.id;
        },
        parseYoutubeId(url) {
            const result = (url || '').split(/(vi\/|v=|\/v\/|youtu\.be\/|\/embed\/)/);
            return (result[2] !== undefined) ? result[2].split(/[^0-9a-z_\-]/i)[0] : false;
        },
        async loadInboxPublication() {
            try {
                if (!this.inboxId) return;
                
                // Make sure we have the languages and geographicRegions loaded
                await Promise.all([
                    this.$store.dispatch('topics/loadAll'),
                    this.$store.dispatch('languages/loadAll'),
                    this.$store.dispatch('geographicRegions/loadAll'),
                    this.$store.dispatch('inbox/load', this.inboxId)
                ]);
                
                if (this.inboxItem && this.inboxItem.data && this.inboxItem.data.publication) {
                    this.publication = {...this.inboxItem.data.publication};
                }
            } catch (error) {
                console.error('Error loading inbox publication:', error);
                this.modal = {
                    type: 'error',
                    message: 'Fehler beim Laden der Publikation aus dem Posteingang.'
                };
            }
        },
    },
    created () {
        this.reload();
        this.inboxId = this.$route.query.inboxId;
        if (this.inboxId) {
            this.loadInboxPublication();
        }
    }
}
</script>
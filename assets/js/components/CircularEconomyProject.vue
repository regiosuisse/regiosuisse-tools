<template>

    <div class="circular-economy-project-component">

        <div class="circular-economy-project-component-form">

            <div class="circular-economy-project-component-form-header">

                <h3>Eintrag erstellen</h3>

                <div class="circular-economy-project-component-form-header-actions">
                    <a class="button warning" @click="circularEconomyProject.isPublic = true" v-if="!circularEconomyProject.isPublic">Entwurf</a>
                    <a class="button success" @click="circularEconomyProject.isPublic = false" v-if="circularEconomyProject.isPublic">Öffentlich</a>
                    <a @click="locale = 'de'" class="button" :class="{primary: locale === 'de'}">DE</a>
                    <a @click="locale = 'fr'" class="button" :class="{primary: locale === 'fr'}">FR</a>
                    <a @click="locale = 'it'" class="button" :class="{primary: locale === 'it'}">IT</a>
                    <a class="button error" @click="clickDelete()" v-if="circularEconomyProject.id">Löschen</a>
                    <a class="button warning" @click="clickCancel()">Abbrechen</a>
                    <a class="button primary" @click="clickSave()">Speichern</a>
                </div>

            </div>

            <div class="circular-economy-project-component-form-section">

                <div class="row">
                    <div class="col-md-6" v-if="locale === 'de'">
                        <label for="title">Bezeichnung</label>
                        <input id="title" type="text" class="form-control" v-model="circularEconomyProject.title" :placeholder="translate('title', circularEconomyProject)">
                    </div>
                    <div class="col-md-6" v-else>
                        <label for="title">Bezeichnung (Übersetzung {{ locale.toUpperCase() }})</label>
                        <input id="title" type="text" class="form-control" v-model="circularEconomyProject.translations[locale].title" :placeholder="translate('title', circularEconomyProject)">
                    </div>
                    <div class="col-md-2">
                        <label for="type">Typ</label>
                        <div class="select-wrapper">
                            <select class="form-control" v-model="circularEconomyProject.type">
                                <option value="project">Projekt</option>
                                <option value="example">Beispielhaft</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8" v-if="locale === 'de'">
                        <label for="description">Text</label>
                        <ckeditor id="description" :editor="editor" :config="editorConfig"
                                  v-model="circularEconomyProject.description" :placeholder="translate('description', circularEconomyProject)"></ckeditor>
                    </div>
                    <div class="col-md-8" v-else>
                        <label for="description">Text (Übersetzung {{ locale.toUpperCase() }})</label>
                        <ckeditor id="description" :editor="editor" :config="editorConfig"
                                  v-model="circularEconomyProject.translations[locale].description" :placeholder="translate('description', circularEconomyProject)"></ckeditor>
                    </div>
                </div>

                <div class="circular-economy-project-component-form-section-group">

                    <div class="circular-economy-project-component-form-section-group-headline">Kategorisierung</div>

                    <div class="row">
                        <div class="col-md-3">
                            <label for="topics">Thema</label>
                            <tag-selector id="topics" :model="circularEconomyProject.topics"
                                          :options="topics.filter(e => !e.context || e.context === 'circularEconomyProject')" :searchType="'select'"></tag-selector>
                        </div>
                        <div class="col-md-3">
                            <label for="businessSectors">Geschäftsfelder</label>
                            <tag-selector id="businessSectors" :model="circularEconomyProject.businessSectors"
                                          :options="businessSectors.filter(e => !e.context || e.context === 'circularEconomyProject')" :searchType="'select'"></tag-selector>
                        </div>
                        <div class="col-md-3">
                            <label for="businessSectors">Geographische Region</label>
                            <tag-selector id="geographicRegions" :model="circularEconomyProject.geographicRegions"
                                          :options="geographicRegions.filter(e => !e.context || e.context === 'circularEconomyProject')" :searchType="'select'"></tag-selector>
                        </div>
                        <div class="col-md-3">
                            <label for="instruments">Finanzierung</label>
                            <tag-selector id="instruments" :model="circularEconomyProject.instruments"
                                          :options="instruments.filter(e => !e.context || e.context === 'circularEconomyProject')" :searchType="'select'"></tag-selector>
                        </div>
                    </div>

                </div>

                <div class="circular-economy-project-component-form-section-group">

                    <div class="circular-economy-project-component-form-section-group-headline">Weiterführende Informationen</div>

                    <div class="row">
                        <div class="col-md-8">
                            <label v-if="locale === 'de'">Links</label>
                            <label v-else>Links (Übersetzung {{ locale.toUpperCase() }})</label>
                            <div class="row" v-for="(link, index) in (locale === 'de' ? circularEconomyProject.links : circularEconomyProject.translations[locale].links)">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" v-model="link.label" placeholder="Bezeichnung">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" v-model="link.value" placeholder="URL">
                                </div>
                                <div class="col-md-3">
                                    <button class="button error" @click="clickRemoveLink(index)">Link entfernen</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <button class="button success" @click="clickAddLink()">Link hinzufügen</button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <label v-if="locale === 'de'">Videos</label>
                            <label v-else>Videos (Übersetzung {{ locale.toUpperCase() }})</label>
                            <div class="row" v-for="(video, index) in (locale === 'de' ? circularEconomyProject.videos : circularEconomyProject.translations[locale].videos)">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" v-model="video.label" placeholder="Bezeichnung">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" v-model="video.value" placeholder="URL">
                                </div>
                                <div class="col-md-3">
                                    <button class="button error" @click="clickRemoveVideo(index)">Video entfernen</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <button class="button success" @click="clickAddVideo()">Video hinzufügen</button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <label for="images">Bilder</label>
                            <image-selector id="images" :items="circularEconomyProject.images" :locale="locale" @changed="updateImages"></image-selector>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <label for="files">Dokumente</label>
                            <file-selector id="files" :items="circularEconomyProject.files" :locale="locale" @changed="updateFiles"></file-selector>
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
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import TagSelector from './TagSelector';
import ImageSelector from './ImageSelector';
import FileSelector from './FileSelector';
import Modal from './Modal';
import {DatePicker} from 'v-calendar';

export default {
    data() {
        return {
            locale: 'de',
            circularEconomyProject: {
                isPublic: false,
                type: 'project',
                title: '',
                keywords: '',
                description: '',
                topics: [],
                businessSectors: [],
                geographicRegions: [],
                instruments: [],
                countries: [],
                states: [],
                startDate: [],
                endDate: [],
                links: [],
                videos: [],
                images: [],
                files: [],
                translations: {
                    fr: {
                        links: [],
                        videos: [],
                    },
                    it: {
                        links: [],
                        videos: [],
                    },
                },
            },
            showPreview: false,
            modal: null,
            editor: ClassicEditor,
            editorConfig: {
                basicEntities: false,
                toolbar: {
                    items: [
                        'heading',
                        '|',
                        'bold',
                        'italic',
                        'link',
                        '|',
                        'numberedList',
                        'bulletedList',
                        'insertTable',
                        '|',
                        'undo',
                        'redo',
                    ]
                }
            },
            simpleEditorConfig: {
                basicEntities: false,
                toolbar: {
                    items: [
                        'bold',
                        'italic',
                        'link',
                        '|',
                        'numberedList',
                        'bulletedList',
                        'insertTable',
                    ]
                }
            },
        };
    },
    components: {
        TagSelector,
        ImageSelector,
        FileSelector,
        DatePicker,
        draggable,
        Modal,
    },
    computed: {
        ...mapState({
            selectedCircularEconomyProject: state => state.circularEconomyProjects.circularEconomyProject,
            topics: state => state.topics.all,
            businessSectors: state => state.businessSectors.all,
            geographicRegions: state => state.geographicRegions.all,
            instruments: state => state.instruments.all,
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
                            this.$store.dispatch('circularEconomyProjects/delete', this.circularEconomyProject.id).then(() => {
                                this.$router.push('/circular-economy-projects');
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
            this.$router.push('/circular-economy-projects');
        },
        clickSave() {

            if(this.circularEconomyProject.id) {
                return this.$store.dispatch('circularEconomyProjects/update', this.circularEconomyProject).then(() => {
                    this.$router.push('/circular-economy-projects');
                });
            }

            this.$store.dispatch('circularEconomyProjects/create', this.circularEconomyProject).then(() => {
                this.$router.push('/circular-economy-projects');
            });

        },
        reload() {
            if(this.$route.params.id) {
                this.$store.commit('circularEconomyProjects/set', {});
                this.$store.dispatch('circularEconomyProjects/load', this.$route.params.id).then(() => {
                    this.circularEconomyProject = {...this.selectedCircularEconomyProject};

                    if(!this.circularEconomyProject.translations['fr'].videos) {
                        this.circularEconomyProject.translations['fr'].videos = [];
                    }

                    if(!this.circularEconomyProject.translations['it'].videos) {
                        this.circularEconomyProject.translations['it'].videos = [];
                    }
                });
            }
        },
        clickAddLink() {
            (this.locale === 'de' ? this.circularEconomyProject.links : this.circularEconomyProject.translations[this.locale].links).push({
                value: '',
                label: '',
            });
        },
        clickRemoveLink(index) {
            let link = (this.locale === 'de' ? this.circularEconomyProject.links : this.circularEconomyProject.translations[this.locale].links).splice(index, 1)[0];
        },
        clickAddVideo() {
            (this.locale === 'de' ? this.circularEconomyProject.videos : this.circularEconomyProject.translations[this.locale].videos).push({
                value: '',
                label: '',
            });
        },
        clickRemoveVideo(index) {
            let video = (this.locale === 'de' ? this.circularEconomyProject.videos : this.circularEconomyProject.translations[this.locale].videos).splice(index, 1)[0];
        },
        updateImages(images) {
            this.circularEconomyProject.images = images;
        },
        updateFiles(files) {
            this.circularEconomyProject.files = files;
        },
        translate(property, context) {
            if(this.locale === 'de') {
                return context[property] || context.translations.fr[property] || context.translations.it[property];
            }
            if(this.locale === 'fr') {
                return context.translations.fr[property] || context[property] || context.translations.it[property];
            }
            if(this.locale === 'it') {
                return context.translations.it[property] || context.translations.fr[property] || context[property];
            }
            return context[property];
        },
        parseYoutubeId(url) {
            const result = (url || '').split(/(vi\/|v=|\/v\/|youtu\.be\/|\/embed\/)/);
            return (result[2] !== undefined) ? result[2].split(/[^0-9a-z_\-]/i)[0] : false;
        },
    },
    created () {
        this.reload();
    }
}
</script>
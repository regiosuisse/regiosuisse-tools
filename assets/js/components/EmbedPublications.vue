<template>

    <div class="embed-publications" :class="[$env.INSTANCE_ID+'-publications', {'is-responsive': responsive}]" @click.stop="clickInside">

        <div class="embed-publications-search">

            <div class="embed-publications-search-input">
                <input type="text" :placeholder="$t('Suchbegriff', locale)" v-model="term"
                       :class="{'has-value': term}"
                       @change="changeSearchTerm()"
                       @keyup="$event.keyCode === 13 ? changeSearchTerm() : null">
                <div class="embed-publications-search-input-icon" @click.stop="term = null; changeSearchTerm()"></div>
            </div>

        </div>

        <div class="embed-publications-filters">

            <div class="embed-publications-filters-select" data-filter-type="topics">

                <div class="embed-publications-filters-select-label"
                     @click.stop="clickFilterSelect('topic')">{{ $t('Thema', locale) }}</div>

                <div class="embed-publications-filters-select-icon"
                     :class="{'is-active': activeFilterSelect === 'topic'}"></div>

                <transition name="embed-publications-filters-select-options" mode="out-in">

                    <div class="embed-publications-filters-select-options" v-if="activeFilterSelect === 'topic'">

                        <div class="embed-publications-filters-select-options-item"
                             v-for="topic in topics"
                             :class="{ 'is-selected': isFilterSelected({ type: 'topic', entity: topic }) }"
                             @click.stop="clickToggleFilter({ type: 'topic', entity: topic })">
                            {{ translateField(topic, 'name', locale) }}
                        </div>

                    </div>

                </transition>

            </div>

            <div class="embed-publications-filters-select" data-filter-type="geographicRegions">

                <div class="embed-publications-filters-select-label"
                     @click.stop="clickFilterSelect('geographicRegion')">{{ $t('Geographische Region', locale) }}</div>

                <div class="embed-publications-filters-select-icon"
                     :class="{'is-active': activeFilterSelect === 'geographicRegion'}"></div>

                <transition name="embed-publications-filters-select-options" mode="out-in">

                    <div class="embed-publications-filters-select-options" v-if="activeFilterSelect === 'geographicRegion'">

                        <div class="embed-publications-filters-select-options-item"
                             v-for="geographicRegion in geographicRegions"
                             :class="{ 'is-selected': isFilterSelected({ type: 'geographicRegion', entity: geographicRegion }) }"
                             @click.stop="clickToggleFilter({ type: 'geographicRegion', entity: geographicRegion })">
                            {{ translateField(geographicRegion, 'name', locale) }}
                        </div>

                    </div>

                </transition>

            </div>

            <div class="embed-publications-filters-select" data-filter-type="languages">

                <div class="embed-publications-filters-select-label"
                     @click.stop="clickFilterSelect('language')">{{ $t('Sprache', locale) }}</div>

                <div class="embed-publications-filters-select-icon"
                     :class="{'is-active': activeFilterSelect === 'language'}"></div>

                <transition name="embed-publications-filters-select-options" mode="out-in">

                    <div class="embed-publications-filters-select-options" v-if="activeFilterSelect === 'language'">

                        <div class="embed-publications-filters-select-options-item"
                             v-for="language in languages"
                             :class="{ 'is-selected': isFilterSelected({ type: 'language', entity: language }) }"
                             @click.stop="clickToggleFilter({ type: 'language', entity: language })">
                            {{ translateField(language, 'name', locale) }}
                        </div>

                    </div>

                </transition>

            </div>

            <div class="embed-publications-filters-select" data-filter-type="years">

                <div class="embed-publications-filters-select-label"
                     @click.stop="clickFilterSelect('year')">{{ $t('Jahr', locale) }}</div>

                <div class="embed-publications-filters-select-icon"
                     :class="{'is-active': activeFilterSelect === 'year'}"></div>

                <transition name="embed-publications-filters-select-options" mode="out-in">

                    <div class="embed-publications-filters-select-options" v-if="activeFilterSelect === 'year'">

                        <div class="embed-publications-filters-select-options-item"
                             v-for="year in years"
                             :class="{ 'is-selected': isFilterSelected({ type: 'year', entity: year }) }"
                             @click.stop="clickToggleFilter({ type: 'year', entity: year })">
                            {{ translateField(year, 'name', locale) }}
                        </div>

                    </div>

                </transition>

            </div>

            <div class="embed-publications-filters-select" data-filter-type="types">

                <div class="embed-publications-filters-select-label"
                     @click.stop="clickFilterSelect('type')">{{ $t('Typ', locale) }}</div>

                <div class="embed-publications-filters-select-icon"
                     :class="{'is-active': activeFilterSelect === 'type'}"></div>

                <transition name="embed-publications-filters-select-options" mode="out-in">

                    <div class="embed-publications-filters-select-options" v-if="activeFilterSelect === 'type'">

                        <div class="embed-publications-filters-select-options-item"
                             v-for="type in types"
                             :class="{ 'is-selected': isFilterSelected({ type: 'type', entity: type }) }"
                             @click.stop="clickToggleFilter({ type: 'type', entity: type })">
                            {{ translateField(type, 'name', locale) }}
                        </div>

                    </div>

                </transition>

            </div>

            <div class="embed-publications-filters-select" data-filter-type="licenseTypes">

                <div class="embed-publications-filters-select-label"
                     @click.stop="clickFilterSelect('licenseType')">{{ $t('Lizenz', locale) }}</div>

                <div class="embed-publications-filters-select-icon"
                     :class="{'is-active': activeFilterSelect === 'licenseType'}"></div>

                <transition name="embed-publications-filters-select-options" mode="out-in">

                    <div class="embed-publications-filters-select-options" v-if="activeFilterSelect === 'licenseType'">

                        <div class="embed-publications-filters-select-options-item"
                             v-for="licenseType in licenseTypes"
                             :class="{ 'is-selected': isFilterSelected({ type: 'licenseType', entity: licenseType }) }"
                             @click.stop="clickToggleFilter({ type: 'licenseType', entity: licenseType })">
                            {{ translateField(licenseType, 'name', locale) }}
                        </div>

                    </div>

                </transition>

            </div>

            <div class="embed-publications-filters-list">

                <div class="embed-publications-filters-list-item"
                     v-for="filter in filters"
                     @click.stop="clickToggleFilter(filter)">{{ translateField(filter.entity, 'name', locale) }}</div>

            </div>

        </div>

        <div class="embed-publications-filterbar">
            <button class="button primary add-publication-button" @click="showPublicationModal = true">{{ $t('publication.submit', locale) }}</button>
        </div>

        <transition name="embed-publications-list" mode="out-in">

            <div class="embed-publications-list" v-if="!isLoading">

                <div class="embed-publications-list-item"
                     v-for="publication in publications" :id="'publication-'+publication.id"
                     @click="clickShowPublication(publication)"
                     :class="{'is-draft': publication.isPublic !== true}">

                    <div class="embed-publications-list-item-content">

                        <h3 class="embed-publications-list-item-content-title">
                            {{ translateField(publication, 'title', locale) }}
                        </h3>

                        <p class="embed-publications-list-item-content-description">{{ $helpers.textExcerpt(translateField(publication, 'description', locale), 512) }}</p>

                        <div class="embed-publications-list-item-content-attributes">

                            <div class="embed-publications-list-item-content-attributes-item">
                                <p><strong>{{ $t('Typ', locale) }}</strong></p>
                                <p v-if="publication.type === 'project'">Projekt</p>
                                <p v-else>Publikation</p>
                            </div>

                            <div class="embed-publications-list-item-content-attributes-item">
                                <p><strong>{{ $t('Autor:innen', locale) }}</strong></p>
                                <p v-html="publication.authors.map(e => e.name).join('<br>') || '-'"></p>
                            </div>

                            <div class="embed-publications-list-item-content-attributes-item">
                                <p><strong>{{ $t('Organisation / Institution', locale) }}</strong></p>
                                <p v-html="publication.organizations.map(e => e.name).join('<br>') || '-'"></p>
                            </div>

                            <div class="embed-publications-list-item-content-attributes-item" v-if="publication.startDate === publication.endDate">
                                <p><strong>{{ $t('Jahr', locale) }}</strong></p>
                                <p>{{ $helpers.formatDate(publication.startDate, 'YYYY') || '-' }}</p>
                            </div>

                            <div class="embed-publications-list-item-content-attributes-item" v-else-if="publication.startDate && publication.endDate">
                                <p><strong>{{ $t('Zeitraum', locale) }}</strong></p>
                                <p>{{ $helpers.formatDateRange(publication.startDate, publication.endDate, 'YYYY') || '-' }}</p>
                            </div>

                            <div class="embed-publications-list-item-content-attributes-item" v-else>
                                <p><strong>{{ $t('Jahr', locale) }}</strong></p>
                                <p>-</p>
                            </div>

                            <div class="embed-publications-list-item-content-attributes-item">
                                <p><strong>{{ $t('Lizenz', locale) }}</strong></p>
                                <p v-if="publication.licenseType !== 'Other'">
                                    <a :href="licenseTypes.find(e => e.type === publication.licenseType).url" target="_blank" @click.stop>
                                        {{ translateField(licenseTypes.find(e => e.type === publication.licenseType), 'name', locale) || '-' }}
                                    </a>
                                </p>
                                <p v-else-if="publication.licenseUrl">
                                    <a :href="publication.licenseUrl" target="_blank" @click.stop>
                                        {{ translateField(publication, 'licenseName', locale) || '-' }}
                                    </a>
                                </p>
                                <p v-else>{{ translateField(publication, 'licenseName', locale) || '-' }}</p>
                            </div>

                            <div class="embed-publications-list-item-content-attributes-item">
                                <p><strong>{{ $t('Nutzungsbedingungen', locale) }}</strong></p>
                                <p v-if="publication.licenseType !== 'Other'">
                                    {{ translateField(licenseTypes.find(e => e.type === publication.licenseType), 'attribution', locale) || '-' }}
                                </p>
                                <p v-else>{{ translateField(publication, 'licenseAttribution', locale) || '-' }}</p>
                            </div>

                            <div class="embed-publications-list-item-content-attributes-item">
                                <p><strong>{{ $t('Rechte-Inhaber:in', locale) }}</strong></p>
                                <p>{{ translateField(publication, 'rightsHolder', locale) || '-' }}</p>
                            </div>

                            <div class="embed-publications-list-item-content-attributes-item">
                                <p><strong>{{ $t('Tags', locale) }}</strong></p>
                                <p>{{ (translateField(publication, 'keywords', locale) || '').split(',').map(e => e.trim()).join(', ') || '-' }}</p>
                            </div>

                        </div>

                        <div class="embed-publications-list-item-content-buttons">
                            <a :href="publication.url" target="_blank" class="embed-publications-list-item-content-buttons-button">Anzeigen</a>
                        </div>

                    </div>

                </div>

            </div>

        </transition>

        <PublicationModal v-if="showPublicationModal" @close="showPublicationModal = false">
            <div class="publication-form-modal">
                <div class="publication-form-header">
                    <img :src="$env.THEME_LOGO" alt="regiosuisse Logo" class="regiosuisse-logo">
                    <h3 class="modal-title">{{ $t('publication.submit.new', locale) }}</h3>
                    <p class="header-description">
                        {{ $t('publication.submit.description', locale) }}
                    </p>
                </div>
                <form @submit.prevent="submitPublication" class="publication-form">
                    <div class="publication-form-columns">
                        <div class="publication-form-column">
                            <div class="form-group">
                                <label for="publicationTitle">{{ $t('publication.title', locale) }}</label>
                                <small class="help-text">{{ $t('publication.title.help', locale) }}</small>
                                <input id="publicationTitle" class="form-control" v-model="newPublication.title" required />
                            </div>
                            <div class="form-group">
                                <label for="publicationDescription">{{ $t('publication.description', locale) }}</label>
                                <small class="help-text">{{ $t('publication.description.help', locale) }}</small>
                                <textarea id="publicationDescription" class="form-control" rows="5" v-model="newPublication.description" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="publicationType">{{ $t('publication.type', locale) }}</label>
                                <small class="help-text">{{ $t('publication.type.help', locale) }}</small>
                                <select id="publicationType" class="form-control" v-model="newPublication.type" required>
                                    <option :value="'publication'">{{ $t('Publikation') }}</option>
                                    <option :value="'project'">{{ $t('Projekt') }}</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="publicationUrl">{{ $t('publication.url', locale) }}</label>
                                <small class="help-text">{{ $t('publication.url.help', locale) }}</small>
                                <input id="publicationUrl" type="text" class="form-control" v-model="newPublication.url" />
                            </div>
                            <div class="form-group">
                                <label for="publicationStartDate" v-if="newPublication.type === 'project'">{{ $t('publication.startDate', locale) }}</label>
                                <label for="publicationStartDate" v-else>{{ $t('publication.publicationDate', locale) }}</label>
                                <small class="help-text" v-if="newPublication.type === 'project'">{{ $t('publication.startDate.help', locale) }}</small>
                                <small class="help-text" v-else>{{ $t('publication.publicationDate.help', locale) }}</small>
                                <date-picker mode="date" :is24hr="true" v-model="newPublication.startDate" :locale="'de'" @update:modelValue="newPublication.endDate = newPublication.startDate">
                                    <template v-slot="{ inputValue, inputEvents }">
                                        <input type="text"
                                               id="publicationStartDate"
                                               class="form-control"
                                               :value="inputValue"
                                               v-on="inputEvents">
                                    </template>
                                </date-picker>
                            </div>
                            <div class="form-group" v-if="newPublication.type === 'project'">
                                <label for="publicationEndDate">{{ $t('publication.endDate', locale) }}</label>
                                <small class="help-text">{{ $t('publication.endDate.help', locale) }}</small>
                                <date-picker mode="date" :is24hr="true" v-model="newPublication.endDate" :locale="'de'">
                                    <template v-slot="{ inputValue, inputEvents }">
                                        <input type="text"
                                               id="publicationEndDate"
                                               class="form-control"
                                               :value="inputValue"
                                               v-on="inputEvents">
                                    </template>
                                </date-picker>
                            </div>
                            <div class="form-group">
                                <label>{{ $t('publication.authors', locale) }}</label>
                                <small class="help-text">{{ $t('publication.authors.help', locale) }}</small>
                                <div class="links-list">
                                    <div v-for="(link, index) in newPublication.authors" :key="index" class="link-item">
                                        <input type="text" class="form-control" v-model="link.name" :placeholder="$t('publication.authors.label', locale)">
                                        <button type="button" class="button error" @click="newPublication.authors.splice(index, 1)">{{ $t('publication.authors.remove', locale) }}</button>
                                    </div>
                                </div>
                                <button type="button" class="button primary" @click="newPublication.authors.push({ name: '' })">{{ $t('publication.authors.add', locale) }}</button>
                            </div>
                            <div class="form-group">
                                <label>{{ $t('publication.organizations', locale) }}</label>
                                <small class="help-text">{{ $t('publication.organizations.help', locale) }}</small>
                                <div class="links-list">
                                    <div v-for="(link, index) in newPublication.organizations" :key="index" class="link-item">
                                        <input type="text" class="form-control" v-model="link.name" :placeholder="$t('publication.organizations.label', locale)">
                                        <button type="button" class="button error" @click="newPublication.organizations.splice(index, 1)">{{ $t('publication.organizations.remove', locale) }}</button>
                                    </div>
                                </div>
                                <button type="button" class="button primary" @click="newPublication.organizations.push({ name: '' })">{{ $t('publication.organizations.add', locale) }}</button>
                            </div>
                        </div>
                        <div class="publication-form-column">
                            <div class="form-group">
                                <label for="licenseType">{{ $t('publication.licenseType', locale) }}</label>
                                <small class="help-text">{{ $t('publication.licenseType.help', locale) }}</small>
                                <select id="licenseType" class="form-control" v-model="newPublication.licenseType">
                                    <option v-for="licenseType in licenseTypes" :key="licenseType.type" :value="licenseType.type">
                                        {{ licenseType.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="rightsHolder">{{ $t('publication.rightsHolder', locale) }}</label>
                                <small class="help-text">{{ $t('publication.rightsHolder.help', locale) }}</small>
                                <input id="rightsHolder" type="text" class="form-control" v-model="newPublication.rightsHolder" />
                            </div>
                            <div v-if="newPublication.licenseType === 'Other'">
                                <div class="form-group">
                                    <label for="licenseName">{{ $t('publication.licenseName', locale) }}</label>
                                    <small class="help-text">{{ $t('publication.licenseName.help', locale) }}</small>
                                    <input id="licenseName" type="text" class="form-control" v-model="newPublication.licenseName" />
                                </div>
                                <div class="form-group">
                                    <label for="licenseUrl">{{ $t('publication.licenseUrl', locale) }}</label>
                                    <small class="help-text">{{ $t('publication.licenseUrl.help', locale) }}</small>
                                    <input id="licenseUrl" type="text" class="form-control" v-model="newPublication.licenseUrl" />
                                </div>
                                <div class="form-group">
                                    <label for="licenseAttribution">{{ $t('publication.licenseAttribution', locale) }}</label>
                                    <small class="help-text">{{ $t('publication.licenseAttribution.help', locale) }}</small>
                                    <input id="licenseAttribution" type="text" class="form-control" v-model="newPublication.licenseAttribution" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="publicationGeographicRegion">{{ $t('publication.geographicRegion', locale) }}</label>
                                <small class="help-text">{{ $t('publication.geographicRegion.help', locale) }}</small>
                                <tag-selector id="publicationGeographicRegion"
                                              :locale="locale"
                                              :model="newPublication.geographicRegions"
                                              :options="geographicRegions.filter(geographicRegion => !geographicRegion.context || geographicRegion.context === 'publication')"
                                              :searchType="'select'"
                                              required>
                                </tag-selector>
                            </div>
                            <div class="form-group">
                                <label for="publicationTopic">{{ $t('publication.topic', locale) }}</label>
                                <small class="help-text">{{ $t('publication.topic.help', locale) }}</small>
                                <tag-selector id="publicationTopic"
                                              :locale="locale"
                                              :model="newPublication.topics"
                                              :options="topics.filter(topic => !topic.context || topic.context === 'publication')"
                                              :searchType="'select'"
                                              required>
                                </tag-selector>
                            </div>
                            <div class="form-group">
                                <label for="publicationLanguage">{{ $t('publication.language', locale) }}</label>
                                <small class="help-text">{{ $t('publication.language.help', locale) }}</small>
                                <tag-selector id="publicationLanguage"
                                              :locale="locale"
                                              :model="newPublication.languages"
                                              :options="languages.filter(language => !language.context || language.context === 'publication')"
                                              :searchType="'select'"
                                              required>
                                </tag-selector>
                            </div>
                            <div class="form-group">
                                <label for="publicationKeywords">{{ $t('publication.keywords', locale) }}</label>
                                <small class="help-text">{{ $t('publication.keywords.help', locale) }}</small>
                                <textarea id="publicationKeywords" class="form-control" rows="1" v-model="newPublication.keywords" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="publicationEmail">{{ $t('publication.email', locale) }}</label>
                                <small class="help-text">{{ $t('publication.email.help', locale) }}</small>
                                <input type="email" class="form-control" v-model="newPublication.email" required>
                            </div>
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="button warning" @click="showPublicationModal = false">{{ $t('publication.cancel', locale) }}</button>
                        <button type="submit" class="button primary">{{ $t('publication.save', locale) }}</button>
                    </div>
                </form>
            </div>
        </PublicationModal>

    </div>

</template>

<script>

import {mapGetters, mapState} from 'vuex';
import { translateField } from '../utils/filters';
import {track, trackDevice, trackPageView} from '../utils/logger';
import PublicationModal from './PublicationModal.vue';
import TagSelector from './TagSelector';
import FileSelector from './FileSelector';
import { DatePicker } from 'v-calendar';
import 'v-calendar/dist/style.css';
import {licenseTypes} from '../common/licenseTypes';
import moment from 'moment';

export default {

    components: {
        PublicationModal,
        TagSelector,
        FileSelector,
        DatePicker,
    },

    data() {
        return {
            licenseTypes,
            isLoading: false,
            publications: [],
            term: '',
            filters: [],
            activeFilterSelect: null,
            publication: null,
            showPublicationModal: false,
            newPublication: {
                email: '',
                isPublic: false,
                title: '',
                keywords: '',
                description: '',
                type: 'publication',
                url: null,
                startDate: null,
                endDate: null,
                licenseType: 'CC0',
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
        };
    },

    computed: {
        locale () {
            return this.$clientOptions?.locale || 'de';
        },
        responsive () {
            return this.$clientOptions?.responsive ?? true;
        },
        fixedFilters () {
            return this.$clientOptions?.fixedFilters || [];
        },
        disableTelemetry () {
            return this.$clientOptions?.disableTelemetry || false;
        },
        disableThumbnails () {
            return this.$clientOptions?.disableThumbnails || false;
        },
        history () {
            return this.$clientOptions?.history || false;
        },
        historyMode () {
            return this.$clientOptions?.history?.mode || 'query';
        },
        historyBase () {
            return this.$clientOptions?.history?.base || '';
        },
        ...mapState({
            geographicRegions: function (state) {
                return state.geographicRegions.all
                    .filter(e => !e.context || e.context === 'publication')
                    .map(this.$clientOptions?.middleware?.mapGeographicRegions || (e => e))
                    .filter(this.$clientOptions?.middleware?.filterGeographicRegions || (e => e.isPublic))
                    .sort(this.$clientOptions?.middleware?.sortGeographicRegions || ((a, b) => a.position - b.position));
            },
            topics: function (state) {
                return state.topics.all
                    .filter(e => !e.context || e.context === 'publication')
                    .map(this.$clientOptions?.middleware?.mapTopics || (e => e))
                    .filter(this.$clientOptions?.middleware?.filterTopics || (e => e.isPublic))
                    .sort(this.$clientOptions?.middleware?.sortTopics || ((a, b) => a.position - b.position));
            },
            languages: function (state) {
                return state.languages.all
                    .filter(e => !e.context || e.context === 'publication')
                    .map(this.$clientOptions?.middleware?.mapLanguages || (e => e))
                    .filter(this.$clientOptions?.middleware?.filterLanguages || (e => e.isPublic))
                    .sort(this.$clientOptions?.middleware?.sortLanguages || ((a, b) => a.position - b.position));
            },
        }),
        ...mapGetters({
            getGeographicRegionById: 'geographicRegions/getById',
            getTopicById: 'topics/getById',
            getLanguageById: 'languages/getById',
        }),
        years() {
            const years = [];

            for (let i = parseInt(moment().format('YYYY')); i > 1900; i--) {
                years.push({
                    id: ''+i,
                    name: ''+i,
                })
            }

            return years;
        },
        types() {
            const types = [
                {
                    id: 'publication',
                    name: 'Publikation',
                    translations: {
                        en: 'Publication',
                        fr: 'Publication',
                        it: 'Pubblicazione',
                    }
                },
                {
                    id: 'project',
                    name: 'Projekt',
                    translations: {
                        en: 'Project',
                        fr: 'Projet',
                        it: 'Progetto',
                    }
                },
            ];

            return types;
        },
    },

    methods: {

        translateField,

        keyUp (event) {

            if(event.keyCode === 27) {
                this.activeFilterSelect = null;
                this.publication = null;
            }

        },

        clickOutside (event) {

            this.activeFilterSelect = null;

        },

        clickInside (event) {

            this.activeFilterSelect = null;

        },

        getFilterParams() {

            let params = {};
            params.term = this.term;

            let filters = [...this.filters, ...(this.fixedFilters || [])];

            for(let filter of filters) {
                params[filter.type] = [];
            }

            for(let filter of filters) {
                if(filter.type === 'licenseType') {
                    params[filter.type].push(filter.entity.type);
                    continue;
                }

                params[filter.type].push(filter.entity?.id || filter.entity?.name);
            }

            return params;

        },

        clickFilterSelect(name) {

            if(this.activeFilterSelect === name) {

                if(!this.disableTelemetry) {
                    track('Publication Filter', 'Hide', name);
                }

                return this.activeFilterSelect = null;
            }

            if(!this.disableTelemetry) {
                track('Publication Filter', 'Show', name);
            }

            this.activeFilterSelect = name;

        },

        clickToggleFilter(filter) {
            this.activeFilterSelect = null;

            let index = this.filters.findIndex(e => e.type === filter.type && e.entity.id === filter.entity.id);

            if(index !== -1) {

                this.filters.splice(index, 1);
                this.reload();

                if(this.history) {
                    window.history.replaceState(null, null, this.getHistoryQueryString());
                }

                if(!this.disableTelemetry) {
                    track('Publication Filter', 'Disable', {
                        type: filter.type,
                        id: filter.entity.id,
                    });
                }

                return;

            }

            this.filters.push(filter);

            this.reload();

            if(this.history) {
                window.history.replaceState(null, null, this.getHistoryQueryString());
            }

            if(!this.disableTelemetry) {
                track('Publication Filter', 'Enable', {
                    type: filter.type,
                    id: filter.entity.id,
                });
            }
        },

        isFilterSelected(filter) {
            return this.filters.find(e => e.type === filter.type && e.entity.id === filter.entity.id);
        },

        changeSearchTerm() {

            this.reload();

            if(this.history) {
                window.history.replaceState(null, null, this.getHistoryQueryString());
            }

            if(!this.disableTelemetry) {
                track('Publication Search', 'Change', this.term);
            }

        },

        clickShowPublication(publication) {

            if(!this.disableTelemetry) {
                track('Publication Navigation', 'Show Publication', {
                    id: publication.id,
                    name: translateField(publication, 'title', this.locale),
                });
            }

            if(publication.url) {
                window.open(translateField(publication, 'url', this.locale), '_blank');
            }

        },

        popState(event) {

            this.publication = null;

            if(this.getUrlParams()['publication-id']) {
                this.$store.dispatch('publications/load', this.getUrlParams()['publication-id']).then((publication) => {
                    this.publication = publication;
                });
            }

        },

        getUrlParams () {
            let queryString = window.location.search;

            if(this.historyMode === 'hash') {
                queryString = window.location.hash.substring(1);
            }

            let urlParams = new URLSearchParams(queryString);
            let result = {};

            for(const [key, value] of urlParams) {

                let k = key.split('[')[0];

                if(!['geographicRegions', 'topics', 'languages', 'years', 'types', 'licenseTypes'].includes(k)) {
                    result[k] = value;
                    continue;
                }

                if(!result[k]) {
                    result[k] = [];
                }

                result[k].push(value);

            }

            return result;
        },

        getHistoryQueryString(publication) {

            let result = [];

            if(publication) {
                result.push('publication-id='+publication.id+'&name='+encodeURIComponent(translateField(publication, 'name', this.locale)));
            }

            if(this.term) {
                result.push('term='+encodeURIComponent(this.term));
            }

            for(let filter of this.filters) {
                result.push(filter.type+'s[]='+encodeURIComponent(translateField(filter.entity, 'name', this.locale)))
            }

            result = result.join('&');

            if(!result) {
                return this.historyBase;
            }

            return this.historyBase + (this.historyMode === 'hash' ? '#' : '') + '?' + result;

        },

        applyFiltersFromUrlParameters() {

            this.term = this.getUrlParams()['term'];

            let filters = [];

            ['geographicRegion', 'topic', 'language', 'year', 'type', 'licenseType'].forEach((key) => {

                let collection = key+'s';

                if(!this.getUrlParams()[key+'s']) {
                    return;
                }

                this.getUrlParams()[key+'s'].forEach((f) => {
                    let entity = this[collection].find(e => e.id === f || e.name === f || translateField(e, 'name', this.locale) === f);

                    if(!entity) {
                        return;
                    }

                    filters.push({
                        type: key,
                        entity: entity,
                    });
                });

            });

            if(filters.length) {
                this.filters = filters;
                this.reload();
            }

        },

        reload() {
            this.isLoading = true;

            return this.$store.dispatch('publications/loadFiltered', this.getFilterParams()).then((publications) => {

                this.publications = [
                    ...publications,
                ];

                this.isLoading = false;

            });
        },

        submitPublication() {
            this.$store.dispatch('publications/createFromEmbed', {...this.newPublication, ...{ contactInfo: { email: this.newPublication.email || null } }, locale: this.locale})
                .then(response => {
                    // Open confirmation page in new tab
                    window.location.href = response.redirectUrl;
                    // Close the modal
                    this.showPublicationModal = false;
                    // Reset form
                    this.newPublication = {
                        email: '',
                        isPublic: false,
                        title: '',
                        keywords: '',
                        description: '',
                        type: 'publication',
                        url: null,
                        startDate: null,
                        endDate: null,
                        licenseType: 'CC BY',
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
                    };
                })
                .catch(error => {
                    console.error('Error creating publication:', error);
                    this.modal = {
                        type: 'error',
                        message: error.response?.data?.error || 'Fehler beim Erstellen der Publikation. Bitte überprüfen Sie alle Pflichtfelder.'
                    };
                });
        },

        addLink() {
            this.newPublication.links.push({
                label: '',
                value: ''
            });
        },

        removeLink(index) {
            this.newPublication.links.splice(index, 1);
        },

        updateFiles(files) {
            this.newPublication.files = files;
        },
    },

    created() {
        this.filters = this.$clientOptions?.defaultFilters || [];

        if(this.history && this.getUrlParams()['term']) {
            this.term = this.getUrlParams()['term'];
        }

        this.reload();

        Promise.all([
            this.$store.dispatch('geographicRegions/loadAll'),
            this.$store.dispatch('topics/loadAll'),
            this.$store.dispatch('languages/loadAll'),
        ]).then(() => {

            this.filters = this.filters
                .filter((filter) => {
                    return ['geographicRegion', 'topic'].includes(filter.type);
                })
                .map((filter) => {
                    return {
                        type: filter.type,
                        entity: {
                            ...this[filter.type+'s'].find(e => e.id === filter.entity.id),
                        },
                    }
                });

            if(this.history) {
                this.applyFiltersFromUrlParameters();
            }

        });

    },

    mounted() {
        window.addEventListener('click', this.clickOutside);
        window.addEventListener('keyup', this.keyUp);

        if(this.history && this.getUrlParams()['publication-id']) {
            this.$store.dispatch('publications/load', this.getUrlParams()['publication-id']).then((publication) => {
                this.publication = publication;
            });
        }

        if(this.history) {
            window.addEventListener('popstate', this.popState);
        }

        if(!this.disableTelemetry) {
            trackDevice();
            trackPageView();
        }
    },

    beforeUnmount() {
        window.removeEventListener('click', this.clickOutside);
        window.removeEventListener('keyup', this.keyUp);
        window.removeEventListener('popstate', this.popState);
    }

};

</script>

<style lang="scss">



</style>

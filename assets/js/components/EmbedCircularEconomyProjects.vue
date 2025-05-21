<template>

    <div class="embed-circular-economy-projects" :class="[$env.INSTANCE_ID+'-circular-economy-projects', {'is-responsive': responsive}]" @click.stop="clickInside">

        <div v-if="templateHook('projectsBefore', locale)" v-html="templateHook('projectsBefore', locale)"></div>

        <div class="embed-circular-economy-projects-content">

            <div class="embed-circular-economy-projects-content-filter">

                <div class="embed-circular-economy-projects-content-filter-section">
                    <div class="embed-circular-economy-projects-content-filter-section-label">
                        {{ $t('Thema', locale) }}
                    </div>
                    <div class="embed-circular-economy-projects-content-filter-section-options">
                        <button class="embed-circular-economy-projects-content-filter-section-options-option"
                                v-for="e in topics"
                                :class="{ 'is-selected': isFilterSelected({ type: 'topic', entity: e }) }"
                                @click="clickToggleFilter({ type: 'topic', entity: e })">
                            <span class="embed-circular-economy-projects-content-filter-section-options-option-label">{{ translateField(e, 'name', locale) }}</span>
                        </button>
                    </div>
                </div>

                <div class="embed-circular-economy-projects-content-filter-section">
                    <div class="embed-circular-economy-projects-content-filter-section-label">
                        {{ $t('Finanzierung', locale) }}
                    </div>
                    <div class="embed-circular-economy-projects-content-filter-section-options">
                        <button class="embed-circular-economy-projects-content-filter-section-options-option"
                                v-for="e in instruments"
                                :class="{ 'is-selected': isFilterSelected({ type: 'instrument', entity: e }) }"
                                @click="clickToggleFilter({ type: 'instrument', entity: e })">
                            <span class="embed-circular-economy-projects-content-filter-section-options-option-label">{{ translateField(e, 'name', locale) }}</span>
                        </button>
                    </div>
                </div>

                <div class="embed-circular-economy-projects-content-filter-section">
                    <div class="embed-circular-economy-projects-content-filter-section-label">
                        {{ $t('Geschäftsfeld', locale) }}
                    </div>
                    <div class="embed-circular-economy-projects-content-filter-section-options">
                        <button class="embed-circular-economy-projects-content-filter-section-options-option"
                                v-for="e in businessSectors"
                                :class="{ 'is-selected': isFilterSelected({ type: 'businessSector', entity: e }) }"
                                @click="clickToggleFilter({ type: 'businessSector', entity: e })">
                            <span class="embed-circular-economy-projects-content-filter-section-options-option-label">{{ translateField(e, 'name', locale) }}</span>
                        </button>
                    </div>
                </div>

                <div class="embed-circular-economy-projects-content-filter-section">
                    <div class="embed-circular-economy-projects-content-filter-section-label">
                        {{ $t('Geographische Region', locale) }}
                    </div>
                    <div class="embed-circular-economy-projects-content-filter-section-options">
                        <button class="embed-circular-economy-projects-content-filter-section-options-option"
                                v-for="e in geographicRegions"
                                :class="{ 'is-selected': isFilterSelected({ type: 'geographicRegion', entity: e }) }"
                                @click="clickToggleFilter({ type: 'geographicRegion', entity: e })">
                            <span class="embed-circular-economy-projects-content-filter-section-options-option-label">{{ translateField(e, 'name', locale) }}</span>
                        </button>
                    </div>
                </div>

            </div>

            <Transition name="fade" mode="out-in">

                <div class="embed-circular-economy-projects-content-grid" v-if="!project">
                    
                    <TransitionGroup name="fade-down" mode="out-in">

                        <button class="embed-circular-economy-projects-content-grid-item"
                                v-for="project in projects"
                                :key="project.id"
                                @click="clickShowProject(project)">

                            <span class="embed-circular-economy-projects-content-grid-item-image" v-if="project.images.length">
                                <img :src="$env.HOST+'/api/v1/files/view/'+ project.images[0].id +'.' + project.images[0].extension" :alt="project.title">
                            </span>

                            <span class="embed-circular-economy-projects-content-grid-item-content">

                                <span class="embed-circular-economy-projects-content-grid-item-content-title">
                                    {{ translateField(project, 'title', locale) }}
                                </span>

                                <span class="embed-circular-economy-projects-content-grid-item-content-description">
                                    {{ $helpers.textExcerpt($helpers.stripHTML(translateField(project, 'description', locale)), 64, '...') }}
                                </span>

                                <span class="embed-circular-economy-projects-content-grid-item-content-tags">

                                    <span class="embed-circular-economy-projects-content-grid-item-content-tags-item"
                                          v-for="e in project.topics.filter(e => getTopicById(e.id)).filter(e => topics.find(ee => ee.id === e.id))">
                                            {{ translateField(getTopicById(e.id), 'name', locale) }}
                                    </span>

                                    <span class="embed-circular-economy-projects-content-grid-item-content-tags-item"
                                          v-for="e in project.instruments.filter(e => getInstrumentById(e.id)).filter(e => instruments.find(ee => ee.id === e.id))">
                                            {{ translateField(getInstrumentById(e.id), 'name', locale) }}
                                    </span>

                                    <span class="embed-circular-economy-projects-content-grid-item-content-tags-item"
                                          v-for="e in project.geographicRegions.filter(e => getGeographicRegionById(e.id)).filter(e => geographicRegions.find(ee => ee.id === e.id))">
                                            {{ translateField(getGeographicRegionById(e.id), 'name', locale) }}
                                    </span>

                                    <span class="embed-circular-economy-projects-content-grid-item-content-tags-item"
                                          v-for="e in project.businessSectors.filter(e => getBusinessSectorById(e.id)).filter(e => businessSectors.find(ee => ee.id === e.id))">
                                            {{ translateField(getBusinessSectorById(e.id), 'name', locale) }}
                                    </span>

                                </span>

                            </span>

                        </button>

                    </TransitionGroup>

                </div>

                <div class="embed-circular-economy-projects-content-project" v-else>

                    <button class="embed-circular-economy-projects-content-project-close"
                            @click="clickHideProject()"></button>

                    <div class="embed-circular-economy-projects-content-project-title">{{ project.title }}</div>

                    <div class="embed-circular-economy-projects-content-project-description" v-if="project.description">
                        <div v-html="project.description"></div>
                    </div>

                    <div class="embed-circular-economy-projects-content-project-videos" v-if="(translateField(project, 'videos', locale) || []).filter(e => e.value).length">

                        <div class="embed-circular-economy-projects-content-project-videos-video" v-for="video in (translateField(project, 'videos', locale) || []).filter(e => e.value)">
                            <iframe :src="parseVideoEmbedUrl(video.value)" frameborder="0" allowfullscreen></iframe>
                        </div>

                    </div>

                    <div class="embed-circular-economy-projects-content-project-attributes">

                        <div class="embed-circular-economy-projects-content-project-attributes-attribute"
                             v-if="(translateField(project, 'files', locale) || []).length">

                            <div class="embed-circular-economy-projects-content-project-attributes-attribute-label">{{ $t('Downloads', locale) }}</div>

                            <div class="embed-circular-economy-projects-content-project-attributes-attribute-values">
                                <div class="embed-circular-economy-projects-content-project-attributes-attribute-values-value"
                                     v-for="(e, index) in (translateField(project, 'files', locale) || [])">
                                    <a :href="$env.HOST+'/api/v1/files/download/'+e.id+'.'+e.extension" download>
                                        {{ e.description || 'Datei '+(index+1) }}
                                    </a>
                                </div>
                            </div>

                        </div>

                        <div class="embed-circular-economy-projects-content-project-attributes-attribute"
                             v-if="(translateField(project, 'links', locale) || []).length">

                            <div class="embed-circular-economy-projects-content-project-attributes-attribute-label">{{ $t('Links', locale) }}</div>

                            <div class="embed-circular-economy-projects-content-project-attributes-attribute-values">
                                <div class="embed-circular-economy-projects-content-project-attributes-attribute-values-value"
                                     v-for="(e, index) in (translateField(project, 'links', locale) || [])">
                                    <a :href="e.value.split('://').length > 1 ? e.value : 'http://'+e.value" target="_blank">{{ e.label || e.value }}</a>
                                </div>
                            </div>

                        </div>

                        <div class="embed-circular-economy-projects-content-project-attributes-attribute"
                             v-if="project.topics?.length">

                            <div class="embed-circular-economy-projects-content-project-attributes-attribute-label">{{ $t('Thema', locale) }}</div>

                            <div class="embed-circular-economy-projects-content-project-attributes-attribute-values">
                                <div class="embed-circular-economy-projects-content-project-attributes-attribute-values-value"
                                     v-for="e in project.topics.map(e => getTopicById(e.id)).filter(e => e)">
                                    {{ translateField(e, 'name', locale) }}
                                </div>
                            </div>

                        </div>

                        <div class="embed-circular-economy-projects-content-project-attributes-attribute"
                             v-if="project.instruments?.length">

                            <div class="embed-circular-economy-projects-content-project-attributes-attribute-label">{{ $t('Finanzierung', locale) }}</div>

                            <div class="embed-circular-economy-projects-content-project-attributes-attribute-values">
                                <div class="embed-circular-economy-projects-content-project-attributes-attribute-values-value"
                                     v-for="e in project.instruments.map(e => getInstrumentById(e.id)).filter(e => e)">
                                    {{ translateField(e, 'name', locale) }}
                                </div>
                            </div>

                        </div>

                        <div class="embed-circular-economy-projects-content-project-attributes-attribute"
                             v-if="project.businessSectors?.length">

                            <div class="embed-circular-economy-projects-content-project-attributes-attribute-label">{{ $t('Geschäftsfeld', locale) }}</div>

                            <div class="embed-circular-economy-projects-content-project-attributes-attribute-values">
                                <div class="embed-circular-economy-projects-content-project-attributes-attribute-values-value"
                                     v-for="e in project.businessSectors.map(e => getBusinessSectorById(e.id)).filter(e => e)">
                                    {{ translateField(e, 'name', locale) }}
                                </div>
                            </div>

                        </div>

                        <div class="embed-circular-economy-projects-content-project-attributes-attribute"
                             v-if="project.geographicRegions?.length">

                            <div class="embed-circular-economy-projects-content-project-attributes-attribute-label">{{ $t('Geographische Region', locale) }}</div>

                            <div class="embed-circular-economy-projects-content-project-attributes-attribute-values">
                                <div class="embed-circular-economy-projects-content-project-attributes-attribute-values-value"
                                     v-for="e in project.geographicRegions.map(e => getGeographicRegionById(e.id)).filter(e => e)">
                                    {{ translateField(e, 'name', locale) }}
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

            </Transition>

        </div>

        <div v-if="templateHook('projectsAfter', locale)" v-html="templateHook('projectsAfter', locale)"></div>

    </div>

</template>

<script>

import {mapGetters, mapState} from 'vuex';
import { translateField } from '../utils/filters';
import {track, trackDevice, trackPageView} from '../utils/logger';

export default {

    components: {
    },

    data() {
        return {
            isLoading: false,
            isLoadingMore: false,
            isLoadedFully: false,
            projects: [],
            term: '',
            filters: [],
            limit: 1000,
            offset: 0,
            activeFilterSelect: null,
            project: null,
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
            topics: function (state) {
                return state.topics.all
                    .filter(e => !e.context || e.context === 'circularEconomyProject')
                    .map(this.$clientOptions?.middleware?.mapTopics || (e => e))
                    .filter(this.$clientOptions?.middleware?.filterTopics || (e => e.isPublic))
                    .sort(this.$clientOptions?.middleware?.sortTopics || ((a, b) => a.position - b.position));
            },
            instruments: function (state) {
                return state.instruments.all
                    .filter(e => !e.context || e.context === 'circularEconomyProject')
                    .map(this.$clientOptions?.middleware?.mapInstruments || (e => e))
                    .filter(this.$clientOptions?.middleware?.filterInstruments || (e => e.isPublic))
                    .sort(this.$clientOptions?.middleware?.sortInstruments || ((a, b) => a.position - b.position));
            },
            geographicRegions: function (state) {
                return state.geographicRegions.all
                    .filter(e => !e.context || e.context === 'circularEconomyProject')
                    .map(this.$clientOptions?.middleware?.mapGeographicRegions || (e => e))
                    .filter(this.$clientOptions?.middleware?.filterGeographicRegions || (e => e.isPublic))
                    .sort(this.$clientOptions?.middleware?.sortGeographicRegions || ((a, b) => a.position - b.position));
            },
            businessSectors: function (state) {
                return state.businessSectors.all
                    .filter(e => !e.context || e.context === 'circularEconomyProject')
                    .map(this.$clientOptions?.middleware?.mapBusinessSectors || (e => e))
                    .filter(this.$clientOptions?.middleware?.filterBusinessSectors || (e => e.isPublic))
                    .sort(this.$clientOptions?.middleware?.sortBusinessSectors || ((a, b) => a.position - b.position));
            },
        }),
        ...mapGetters({
            getStateById: 'states/getById',
            getTopicById: 'topics/getById',
            getInstrumentById: 'instruments/getById',
            getGeographicRegionById: 'geographicRegions/getById',
            getBusinessSectorById: 'businessSectors/getById',
        }),
    },

    methods: {

        translateField,

        templateHook(name, ...params) {
            if(this?.$clientOptions?.templateHooks?.[name]) {
                return this.$clientOptions.templateHooks[name](this, ...params);
            }

            return null;
        },

        keyUp (event) {

            if(event.keyCode === 27) {
                this.activeFilterSelect = null;
                this.project = null;
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
                params[filter.type].push(filter.entity?.id || filter.entity?.name);
            }

            params.limit = this.limit;
            params.offset = this.offset;

            if(this.$clientOptions?.middleware?.modifyQueryParams) {
                params = this.$clientOptions.middleware.modifyQueryParams(params);
            }

            return params;

        },

        clickFilterSelect(name) {

            if(this.activeFilterSelect === name) {

                if(!this.disableTelemetry) {
                    track('Circular Economy Project Filter', 'Hide', name);
                }

                return this.activeFilterSelect = null;
            }

            if(!this.disableTelemetry) {
                track('Circular Economy Project Filter', 'Show', name);
            }

            this.activeFilterSelect = name;

        },

        clickToggleFilter(filter) {
            this.activeFilterSelect = null;

            let index = this.filters.findIndex(e => e.type === filter.type && e.entity.id === filter.entity.id);

            if(this.project) {
                this.clickHideProject();
            }

            if(index !== -1) {

                this.filters.splice(index, 1);
                this.reload();

                if(this.history) {
                    window.history.replaceState(null, null, this.getHistoryQueryString());
                }

                if(!this.disableTelemetry) {
                    track('Circular Economy Project Filter', 'Disable', {
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
                track('Circular Economy Project Filter', 'Enable', {
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
                track('Circular Economy Project Search', 'Change', this.term);
            }

        },

        clickLoadMore() {
            this.isLoadingMore = true;
            this.offset += this.limit;
            let currentCount = this.projects.length;

            return this.$store.dispatch('circularEconomyProjects/loadFiltered', this.getFilterParams()).then((projects) => {

                this.projects = [
                    ...this.projects,
                    ...projects,
                ];

                if(currentCount >= this.projects.length || projects.length < this.limit) {
                    this.isLoadedFully = true;
                }

                this.isLoadingMore = false;

                if(!this.disableTelemetry) {
                    track('Circular Economy Project Navigation', 'Load More', {
                        isLoadedFully: this.isLoadedFully,
                        limit: this.limit,
                        offset: this.offset,
                        count: projects.length,
                    });
                }

            });

        },

        clickShowProject(project) {

            if(this.history) {
                window.history.pushState(null, null, this.getHistoryQueryString(project));
            }

            if(!this.disableTelemetry) {
                track('Circular Economy Project Navigation', 'Show Project', {
                    id: project.id,
                    title: translateField(project, 'title', this.locale),
                });
            }

            this.project = project;

        },

        clickHideProject() {

            if(this.history) {
                window.history.pushState(null, null, this.getHistoryQueryString());
            }

            if(!this.disableTelemetry) {
                track('Circular Economy Project Navigation', 'Hide Project', {
                    id: this.project.id,
                    title: translateField(this.project, 'title', this.locale),
                });
            }

            this.project = null;

        },

        parseVideoEmbedUrl(url) {

            let matches;

            if(url.indexOf('vimeo') !== -1) {
                matches = url.match(/^https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)(?:[?]?.*)$/, url);
                if (matches[3]) {
                    return 'https://player.vimeo.com/video/' + matches[3];
                }
            } else {
                matches = url.match(/^.*(?:(?:youtu\.be\/|v\/|vi\/|u\/\w\/|embed\/)|(?:(?:watch)?\?v(?:i)?=|\&v(?:i)?=))([^#\&\?]*).*/, url);

                if (matches && matches[1]) {
                    return 'https://www.youtube-nocookie.com/embed/' + matches[1];
                }
            }

            return url;
        },

        popState(event) {

            this.project = null;

            if(this.getUrlParams()['project-id']) {
                this.$store.dispatch('circularEconomyProjects/load', this.getUrlParams()['project-id']).then((project) => {
                    this.project = project;
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

                if(!['topics', 'instruments', 'businessSectors', 'geographicRegions'].includes(k)) {
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

        getHistoryQueryString(project) {

            let result = [];

            if(project) {
                result.push('project-id='+project.id+'&title='+encodeURIComponent(translateField(project, 'title', this.locale)));
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

            ['state', 'topic', 'program', 'startDate', 'instrument', 'cooperation'].forEach((key) => {

                let collection = key+'s';

                if(key === 'startDate') {
                    collection = 'years';
                }

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
            this.offset = 0;
            this.isLoadedFully = false;
            this.isLoadingMore = false;

            return this.$store.dispatch('circularEconomyProjects/loadFiltered', this.getFilterParams()).then((projects) => {

                this.projects = [
                    ...projects,
                ];

                if(projects.length < this.limit) {
                    this.isLoadedFully = true;
                }

                this.isLoading = false;

            });
        },

    },

    created() {

        this.limit = this.$clientOptions?.limit || this.limit;
        this.filters = this.$clientOptions?.defaultFilters || [];

        if(this.history && this.getUrlParams()['term']) {
            this.term = this.getUrlParams()['term'];
        }

        this.reload();

        Promise.all([
            this.$store.dispatch('topics/loadAll'),
            this.$store.dispatch('instruments/loadAll'),
            this.$store.dispatch('geographicRegions/loadAll'),
            this.$store.dispatch('businessSectors/loadAll'),
        ]).then(() => {

            this.filters = this.filters
                .filter((filter) => {
                    return ['topic', 'program', 'instrument', 'state'].includes(filter.type);
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

        if(this.history && this.getUrlParams()['project-id']) {
            this.$store.dispatch('circularEconomyProjects/load', this.getUrlParams()['project-id']).then((project) => {
                this.project = project;
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
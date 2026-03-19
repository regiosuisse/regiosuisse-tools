<template>

    <div class="embed-projects" :class="[$env.INSTANCE_ID+'-projects', {'is-responsive': responsive}]" @click.stop="clickInside">

        <div v-if="templateHook('projectsBefore', locale)" v-html="templateHook('projectsBefore', locale)"></div>

        <div class="embed-projects-search">

            <label class="embed-projects-sr-only" :for="`${$env.INSTANCE_ID}-projects-search`">{{ $t('Suchbegriff', locale) }}</label>

            <div class="embed-projects-search-input">
                <input type="text" :placeholder="$t('Suchbegriff', locale)" v-model="term"
                       :class="{'has-value': term}"
                       :id="`${$env.INSTANCE_ID}-projects-search`"
                       @change="changeSearchTerm()"
                       @keyup="$event.keyCode === 13 ? changeSearchTerm() : null">
                <button class="embed-projects-search-input-icon"
                        @click.stop="term = null; changeSearchTerm()"></button>
            </div>

            <button class="embed-projects-search-toggle"
                    v-if="$clientOptions?.mapboxApiToken"
                    data-filter-type="map"
                    @click="clickToggleMap()"
                    :aria-pressed="isMapEnabled ? 'true' : 'false'"
                    :aria-label="$t('Karte anzeigen (NRP und Interreg)', locale)"
            >
                <span class="embed-projects-search-toggle-button"
                      :class="{ 'is-active': isMapEnabled }"></span>
                <span class="embed-projects-search-toggle-label"
                      :class="{ 'is-active': isMapEnabled }">{{ $t('Karte anzeigen (NRP und Interreg)', locale) }}</span>
                <span v-if="templateHook('mapToggleAfter', null)" v-html="templateHook('mapToggleAfter', null)"></span>
            </button>

        </div>

        <div class="embed-projects-filters">

            <div class="embed-projects-filters-select" data-filter-type="states">

                <button class="embed-projects-filters-select-label"
                        :aria-expanded="activeFilterSelect === 'state' ? 'true' : 'false'"
                        :aria-controls="`${$env.INSTANCE_ID}-filter-state`"
                        @click.stop="clickFilterSelect('state')">{{ $t('Kanton', locale) }}</button>

                <div class="embed-projects-filters-select-icon"
                     :class="{'is-active': activeFilterSelect === 'state'}"></div>

                <transition name="embed-projects-filters-select-options" mode="out-in">

                    <div class="embed-projects-filters-select-options"
                         :id="`${$env.INSTANCE_ID}-filter-state`"
                         role="group"
                         :aria-label="$t('Kanton', locale)"
                         v-if="activeFilterSelect === 'state'">

                        <button class="embed-projects-filters-select-options-item"
                                v-for="state in states"
                                :class="{ 'is-selected': isFilterSelected({ type: 'state', entity: state }) }"
                                :aria-pressed="isFilterSelected({ type: 'state', entity: state }) ? 'true' : 'false'"
                                @click.stop="clickToggleFilter({ type: 'state', entity: state })">
                            {{ translateField(state, 'name', locale) }}
                            <span v-if="templateHook('filterSelectOptionsItemAfter', 'state', state)" v-html="templateHook('filterSelectOptionsItemAfter', 'state', state)"></span>
                        </button>

                    </div>

                </transition>

            </div>

            <div class="embed-projects-filters-select" data-filter-type="topics">

                <button class="embed-projects-filters-select-label"
                        :aria-expanded="activeFilterSelect === 'topic' ? 'true' : 'false'"
                        :aria-controls="`${$env.INSTANCE_ID}-filter-topic`"
                        @click.stop="clickFilterSelect('topic')">{{ $t('Thema', locale) }}</button>

                <div class="embed-projects-filters-select-icon"
                     :class="{'is-active': activeFilterSelect === 'topic'}"></div>

                <transition name="embed-projects-filters-select-options" mode="out-in">

                    <div class="embed-projects-filters-select-options"
                         :id="`${$env.INSTANCE_ID}-filter-topic`"
                         role="group"
                         :aria-label="$t('Thema', locale)"
                         v-if="activeFilterSelect === 'topic'">

                        <button class="embed-projects-filters-select-options-item"
                                v-for="topic in topics"
                                :class="{ 'is-selected': isFilterSelected({ type: 'topic', entity: topic }) }"
                                :aria-pressed="isFilterSelected({ type: 'topic', entity: topic }) ? 'true' : 'false'"
                                @click.stop="clickToggleFilter({ type: 'topic', entity: topic })">
                            {{ translateField(topic, 'name', locale) }}
                            <span v-if="templateHook('filterSelectOptionsItemAfter', 'topic', topic)" v-html="templateHook('filterSelectOptionsItemAfter', 'topic', topic)"></span>
                        </button>

                    </div>

                </transition>

            </div>

            <div class="embed-projects-filters-select" data-filter-type="programs">

                <button class="embed-projects-filters-select-label"
                        :aria-expanded="activeFilterSelect === 'program' ? 'true' : 'false'"
                        :aria-controls="`${$env.INSTANCE_ID}-filter-program`"
                        @click.stop="clickFilterSelect('program')">{{ $t('Programm', locale) }}</button>

                <div class="embed-projects-filters-select-icon"
                     :class="{'is-active': activeFilterSelect === 'program'}"></div>

                <transition name="embed-projects-filters-select-options" mode="out-in">

                    <div class="embed-projects-filters-select-options"
                         :id="`${$env.INSTANCE_ID}-filter-program`"
                         role="group"
                         :aria-label="$t('Programm', locale)"
                         v-if="activeFilterSelect === 'program'">

                        <button class="embed-projects-filters-select-options-item"
                                v-for="program in programs"
                                :class="{ 'is-selected': isFilterSelected({ type: 'program', entity: program }) }"
                                :aria-pressed="isFilterSelected({ type: 'program', entity: program }) ? 'true' : 'false'"
                                @click.stop="clickToggleFilter({ type: 'program', entity: program })">
                            {{ translateField(program, 'longName', locale) || translateField(program, 'name', locale) }}
                            <span v-if="templateHook('filterSelectOptionsItemAfter', 'program', program)" v-html="templateHook('filterSelectOptionsItemAfter', 'program', program)"></span>
                            <template v-else-if="translateField(program, 'url', locale)">
                                <a class="embed-projects-filters-select-options-item-icon"
                                   :href="translateField(program, 'url', locale)"
                                   target="_blank" @click.stop data-nosnippet></a>
                            </template>
                        </button>

                    </div>

                </transition>

            </div>

            <div class="embed-projects-filters-select" data-filter-type="startDates">

                <button class="embed-projects-filters-select-label"
                        :aria-expanded="activeFilterSelect === 'startDate' ? 'true' : 'false'"
                        :aria-controls="`${$env.INSTANCE_ID}-filter-startDate`"
                        @click.stop="clickFilterSelect('startDate')">{{ $t('Projektstart', locale) }}</button>

                <div class="embed-projects-filters-select-icon"
                     :class="{'is-active': activeFilterSelect === 'startDate'}"></div>

                <transition name="embed-projects-filters-select-options" mode="out-in">

                    <div class="embed-projects-filters-select-options"
                         :id="`${$env.INSTANCE_ID}-filter-startDate`"
                         role="group"
                         :aria-label="$t('Projektstart', locale)"
                         v-if="activeFilterSelect === 'startDate'">

                        <button class="embed-projects-filters-select-options-item"
                                v-for="startDate in years"
                                :class="{ 'is-selected': isFilterSelected({ type: 'startDate', entity: startDate }) }"
                                :aria-pressed="isFilterSelected({ type: 'startDate', entity: startDate }) ? 'true' : 'false'"
                                @click.stop="clickToggleFilter({ type: 'startDate', entity: startDate })">
                            {{ translateField(startDate, 'name', locale) }}
                            <span v-if="templateHook('filterSelectOptionsItemAfter', 'startDate', startDate)" v-html="templateHook('filterSelectOptionsItemAfter', 'startDate', startDate)"></span>
                        </button>

                    </div>

                </transition>

            </div>

            <div class="embed-projects-filters-select" data-filter-type="instruments">

                <button class="embed-projects-filters-select-label"
                        :aria-expanded="activeFilterSelect === 'instrument' ? 'true' : 'false'"
                        :aria-controls="`${$env.INSTANCE_ID}-filter-instrument`"
                        @click.stop="clickFilterSelect('instrument')">{{ $t('Finanzierung', locale) }}</button>

                <div class="embed-projects-filters-select-icon"
                     :class="{'is-active': activeFilterSelect === 'instrument'}"></div>

                <transition name="embed-projects-filters-select-options" mode="out-in">

                    <div class="embed-projects-filters-select-options"
                         :id="`${$env.INSTANCE_ID}-filter-instrument`"
                         role="group"
                         :aria-label="$t('Finanzierung', locale)"
                         v-if="activeFilterSelect === 'instrument'">

                        <button class="embed-projects-filters-select-options-item"
                             v-for="instrument in instruments"
                             :class="{ 'is-selected': isFilterSelected({ type: 'instrument', entity: instrument }) }"
                             :aria-pressed="isFilterSelected({ type: 'instrument', entity: instrument }) ? 'true' : 'false'"
                             @click.stop="clickToggleFilter({ type: 'instrument', entity: instrument })">
                            {{ translateField(instrument, 'name', locale) }}
                            <span v-if="templateHook('filterSelectOptionsItemAfter', 'instrument', instrument)" v-html="templateHook('filterSelectOptionsItemAfter', 'instrument', instrument)"></span>
                        </button>

                    </div>

                </transition>

            </div>

            <div class="embed-projects-filters-select" data-filter-type="cooperations">

                <button class="embed-projects-filters-select-label"
                        :aria-expanded="activeFilterSelect === 'cooperation' ? 'true' : 'false'"
                        :aria-controls="`${$env.INSTANCE_ID}-filter-cooperation`"
                        @click.stop="clickFilterSelect('cooperation')">{{ $t('Kooperation', locale) }}</button>

                <div class="embed-projects-filters-select-icon"
                     :class="{'is-active': activeFilterSelect === 'cooperation'}"></div>

                <transition name="embed-projects-filters-select-options" mode="out-in">

                    <div class="embed-projects-filters-select-options"
                         :id="`${$env.INSTANCE_ID}-filter-cooperation`"
                         role="group"
                         :aria-label="$t('Kooperation', locale)"
                         v-if="activeFilterSelect === 'cooperation'">

                        <button class="embed-projects-filters-select-options-item"
                                v-for="cooperation in cooperations"
                                :class="{ 'is-selected': isFilterSelected({ type: 'cooperation', entity: cooperation }) }"
                                :aria-pressed="isFilterSelected({ type: 'cooperation', entity: cooperation }) ? 'true' : 'false'"
                                @click.stop="clickToggleFilter({ type: 'cooperation', entity: cooperation })">
                            {{ translateField(cooperation, 'name', locale) }}
                            <span v-if="templateHook('filterSelectOptionsItemAfter', 'cooperation', cooperation)" v-html="templateHook('filterSelectOptionsItemAfter', 'cooperation', cooperation)"></span>
                        </button>

                    </div>

                </transition>

            </div>

            <div class="embed-projects-filters-list">

                <button class="embed-projects-filters-list-item"
                        v-for="filter in filters"
                        :aria-label="`${$t('Filter entfernen', locale)}: ${translateField(filter.entity, 'name', locale)}`"
                        @click.stop="clickToggleFilter(filter)">{{ translateField(filter.entity, 'name', locale) }}</button>

            </div>

        </div>

        <transition name="embed-projects-map" mode="out-in">

            <div class="embed-projects-map" v-if="isMapEnabled">

                <ProjectsMap
                    :locale="locale"
                    :projects="mapProjects"
                    :filters="getFilterParams()"
                    @clickProject="clickMapProject($event)"
                    @clickCluster="clickMapCluster($event)"></ProjectsMap>

            </div>

        </transition>

        <transition name="embed-projects-list" mode="out-in">

            <div class="embed-projects-list" v-if="!isLoading">

                <button class="embed-projects-list-item"
                        v-for="project in projects" :id="'project-'+project.id"
                        :class="{'is-draft': project.isPublic !== true, 'has-image': project.images?.length}"
                        :aria-label="translateField(project, 'title', locale)"
                        @click.stop="clickShowProject(project)">

                    <span class="embed-projects-list-item-header" aria-hidden="true">

                        <span class="embed-projects-list-item-header-image" v-if="project.images.length" :style="{
                            backgroundImage: 'url('+$env.HOST+'/api/v1/files/view/'+ project.images[0].id +'.' + project.images[0].extension+')'
                        }"></span>

                        <span class="embed-projects-list-item-header-image" v-else></span>

                    </span>

                    <span class="embed-projects-list-item-content">

                        <h3 class="embed-projects-list-item-content-title">
                            {{ translateField(project, 'title', locale) }}
                        </h3>

                        <p class="embed-projects-list-item-content-description">
                            {{ $helpers.textExcerpt($helpers.stripHTML(translateField(project, 'description', locale)), 168, '...') }}
                        </p>

                        <span class="embed-projects-list-item-content-tags">

                            <span class="embed-projects-list-item-content-tags-item"
                                 v-for="topic in project.topics.filter(e => getTopicById(e.id)).filter(e => topics.find(ee => ee.id === e.id))">
                                {{ translateField(getTopicById(topic.id), 'name', locale) }}
                            </span>

                            <span class="embed-projects-list-item-content-tags-item"
                                 v-for="program in project.programs.filter(e => getProgramById(e.id)).filter(e => programs.find(ee => ee.id === e.id))">
                                {{ translateField(getProgramById(program.id), 'name', locale) }}
                            </span>

                            <span class="embed-projects-list-item-content-tags-item"
                                 v-for="state in project.states.filter(e => getStateById(e.id)).filter(e => states.find(ee => ee.id === e.id))">
                                {{ translateField(getStateById(state.id), 'name', locale) }}
                            </span>

                            <span class="embed-projects-list-item-content-tags-item" v-if="project.startDate">
                                {{ $helpers.formatDate(project.startDate, 'YYYY') }}
                            </span>

                        </span>

                    </span>

                </button>

            </div>

        </transition>

        <div class="embed-projects-actions" v-if="!isLoading">

            <div class="embed-projects-actions-item" v-if="!isLoadedFully">

                <button class="embed-projects-button" @click="clickLoadMore()" v-if="!isLoadingMore">{{ $t('Mehr Projekte laden', locale) }}</button>
                <button class="embed-projects-button is-disabled"
                        disabled="true"
                        aria-disabled="true"
                        v-else>{{ $t('Projekte werden geladen...', locale) }}</button>

            </div>

        </div>

        <transition name="embed-projects-overlay" mode="out-in">

            <div class="embed-projects-overlay" v-if="project" @click="clickHideProject()"
                 role="dialog"
                 aria-modal="true"
                 ref="projectDialog"
                 tabindex="-1"
                 :aria-label="translateField(project, 'title', locale)">

                <EmbedProjectsView :project="project" :locale="locale" @click.stop
                                   @clickClose="clickHideProject()"></EmbedProjectsView>

            </div>

        </transition>

        <div v-if="templateHook('projectsAfter', locale)" v-html="templateHook('projectsAfter', locale)"></div>

    </div>

</template>

<script>

import {mapGetters, mapState} from 'vuex';
import { translateField } from '../utils/filters';
import moment from 'moment';
import EmbedProjectsView from './EmbedProjectsView';
import {track, trackDevice, trackPageView} from '../utils/logger';
import ProjectsMap from './ProjectsMap.vue';

export default {

    components: {
        EmbedProjectsView,
        ProjectsMap,
    },

    data() {
        return {
            isLoading: false,
            isLoadingMore: false,
            isLoadedFully: false,
            projects: [],
            term: '',
            filters: [],
            limit: 30,
            offset: 0,
            activeFilterSelect: null,
            project: null,
            isMapEnabled: false,
            mapProjects: [],
            lastFocusedElement: null,
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
            states: function (state) {
                return state.states.all
                    .filter(e => !e.context || e.context === 'project')
                    .map(this.$clientOptions?.middleware?.mapStates || (e => e))
                    .filter(this.$clientOptions?.middleware?.filterStates || (e => e.isPublic))
                    .sort(this.$clientOptions?.middleware?.sortStates || ((a, b) => translateField(a, 'name', this.locale).localeCompare(translateField(b, 'name', this.locale))));
            },
            topics: function (state) {
                return state.topics.all
                    .filter(e => !e.context || e.context === 'project')
                    .map(this.$clientOptions?.middleware?.mapTopics || (e => e))
                    .filter(this.$clientOptions?.middleware?.filterTopics || (e => e.isPublic))
                    .sort(this.$clientOptions?.middleware?.sortTopics || ((a, b) => a.position - b.position));
            },
            programs: function (state) {
                return state.programs.all
                    .filter(e => !e.context || e.context === 'project')
                    .map(this.$clientOptions?.middleware?.mapPrograms || (e => e))
                    .filter(this.$clientOptions?.middleware?.filterPrograms || (e => e.isPublic))
                    .sort(this.$clientOptions?.middleware?.sortPrograms || ((a, b) => a.position - b.position));
            },
            instruments: function (state) {
                return state.instruments.all
                    .filter(e => !e.context || e.context === 'project')
                    .map(this.$clientOptions?.middleware?.mapInstruments || (e => e))
                    .filter(this.$clientOptions?.middleware?.filterInstruments || (e => e.isPublic))
                    .sort(this.$clientOptions?.middleware?.sortInstruments || ((a, b) => a.position - b.position));
            },
        }),
        ...mapGetters({
            getStateById: 'states/getById',
            getTopicById: 'topics/getById',
            getProgramById: 'programs/getById',
            getInstrumentById: 'instruments/getById',
        }),
        years () {

            let now = moment('2008-01-01');
            let years = [{
                id: now.format('YYYY-MM-DD'),
                name: 'Vor 2008',
                translations: {
                    fr: 'Avant 2008',
                    it: 'Prima del 2008'
                }
            }];

            do {
                now.add(1, 'year');
                years.push({
                    id: now.format('YYYY-MM-DD'),
                    name: now.format('YYYY')
                });
            } while (now.isBefore(moment().startOf('year')));

            return years.reverse();

        },
        cooperations () {

            return [
                {
                    id: 'cantonal',
                    name: 'kantonal',
                    translations: {
                        fr: 'cantonale',
                        it: 'cantonale'
                    }
                },
                {
                    id: 'inter-cantonal',
                    name: 'überkantonal',
                    translations: {
                        fr: 'intercantonale',
                        it: 'intercantonale'
                    }
                },
                {
                    id: 'national',
                    name: 'national',
                    translations: {
                        fr: 'nationale',
                        it: 'nazionale'
                    }
                },
                {
                    id: 'international',
                    name: 'international',
                    translations: {
                        fr: 'internationale',
                        it: 'internazionale'
                    }
                }
            ];

        },
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

            if(event.key === 'Escape') {
                this.activeFilterSelect = null;
                if(this.project) {
                    this.clickHideProject();
                }
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
            params.randomize = 1;

            if(this.$clientOptions?.middleware?.modifyQueryParams) {
                params = this.$clientOptions.middleware.modifyQueryParams(params);
            }

            return params;

        },

        clickFilterSelect(name) {

            if(this.activeFilterSelect === name) {

                if(!this.disableTelemetry) {
                    track('Project Filter', 'Hide', name);
                }

                return this.activeFilterSelect = null;
            }

            if(!this.disableTelemetry) {
                track('Project Filter', 'Show', name);
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
                    track('Project Filter', 'Disable', {
                        type: filter.type,
                        id: filter.entity.id,
                    });
                }

                return;

            }

            this.filters.push(filter);

            // Show map if program NRP is selected
            if(this.$clientOptions?.mapboxApiToken && filter.type === 'program' && filter.entity.name === 'NRP') {
                this.isMapEnabled = true;
            }

            this.reload();

            if(this.history) {
                window.history.replaceState(null, null, this.getHistoryQueryString());
            }

            if(!this.disableTelemetry) {
                track('Project Filter', 'Enable', {
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
                track('Project Search', 'Change', this.term);
            }

        },

        clickToggleMap() {
            this.isMapEnabled = !this.isMapEnabled;
            track('Project Map Toggle', 'Change', this.isMapEnabled);

            this.filters = !this.isMapEnabled ? [] : [
                {
                    type: 'program',
                    entity: {
                        id: 2,
                        name: 'NRP',
                        translations: {
                            fr: 'NPR',
                            it: 'NPR',
                        },
                    },
                },
                {
                    type: 'program',
                    entity: {
                        id: 1,
                        name: 'NRP-Pilotmassnahmen Bergebiete',
                        translations: {
                            fr: 'Mesures pilotes NPR Régions de montagne NPR',
                            it: 'Misure pilota NPR Regioni di montagna NPR',
                        },
                    },
                },
                {
                    type: 'program',
                    entity: {
                        id: 3,
                        name: 'Interreg',
                        translations: {
                            fr: 'Interreg',
                            it: 'Interreg',
                        },
                    },
                },
            ];

            this.reload();

        },

        clickLoadMore() {
            this.isLoadingMore = true;
            this.offset += this.limit;
            let currentCount = this.projects.length;

            return this.$store.dispatch('projects/loadFiltered', this.getFilterParams()).then((projects) => {

                this.projects = [
                    ...this.projects,
                    ...projects,
                ];

                if(currentCount >= this.projects.length || projects.length < this.limit) {
                    this.isLoadedFully = true;
                }

                this.isLoadingMore = false;

                if(!this.disableTelemetry) {
                    track('Project Navigation', 'Load More', {
                        isLoadedFully: this.isLoadedFully,
                        limit: this.limit,
                        offset: this.offset,
                        count: projects.length,
                    });
                }

            });

        },

        clickShowProject(project) {

            this.lastFocusedElement = document.activeElement;

            if(this.history) {
                window.history.pushState(null, null, this.getHistoryQueryString(project));
            }

            if(!this.disableTelemetry) {
                track('Project Navigation', 'Show Project', {
                    id: project.id,
                    title: translateField(project, 'title', this.locale),
                });
            }

            this.project = project;

            this.$nextTick(() => {
                if(this.$refs.projectDialog) {
                    this.$refs.projectDialog.focus();
                }
            });

        },

        clickHideProject() {

            if(this.history) {
                window.history.pushState(null, null, this.getHistoryQueryString());
            }

            if(!this.disableTelemetry) {
                track('Project Navigation', 'Hide Project', {
                    id: this.project.id,
                    title: translateField(this.project, 'title', this.locale),
                });
            }

            this.project = null;

            this.$nextTick(() => {
                if(this.lastFocusedElement && typeof this.lastFocusedElement.focus === 'function') {
                    this.lastFocusedElement.focus();
                }
            });

        },

        async clickMapProject(id) {

            await this.$store.dispatch('projects/load', id).then((project) => {
                this.clickShowProject(project);
            });

        },

        async clickMapCluster(ids) {

            this.isLoading = true;

            return this.$store.dispatch('projects/loadFiltered', { ids }).then((projects) => {

                this.projects = [
                    ...projects,
                ];

                this.isLoadedFully = true;
                this.isLoading = false;

                setTimeout(() => {
                    document.querySelector('.embed-projects-list').scrollIntoView({
                        behavior: 'smooth',
                    });
                }, 250);

            });

        },

        popState(event) {

            this.project = null;

            if(this.getUrlParams()['project-id']) {
                this.$store.dispatch('projects/load', this.getUrlParams()['project-id']).then((project) => {
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

                if(!['states', 'topics', 'programs', 'startDates', 'instruments', 'cooperations'].includes(k)) {
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

            return this.$store.dispatch('projects/loadFiltered', this.getFilterParams()).then((projects) => {

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
            this.$store.dispatch('states/loadAll'),
            this.$store.dispatch('instruments/loadAll'),
            this.$store.dispatch('programs/loadAll'),
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

            if(this.$clientOptions?.mapboxApiToken && this.filters.find(filter => filter.type === 'program' && filter.entity.name === 'NRP')) {
                this.isMapEnabled = true;
            }

        });

    },

    mounted() {
        window.addEventListener('click', this.clickOutside);
        window.addEventListener('keyup', this.keyUp);

        if(this.history && this.getUrlParams()['project-id']) {
            this.$store.dispatch('projects/load', this.getUrlParams()['project-id']).then((project) => {
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
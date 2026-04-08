<template>

    <div class="embed-financial-supports" :class="[$env.INSTANCE_ID+'-financial-supports', {'is-responsive': responsive}]" @click.stop="clickInside">

        <div class="embed-financial-supports-search">

            <label class="embed-financial-supports-sr-only" :for="`${$env.INSTANCE_ID}-financial-supports`">{{ $t('Suchbegriff', locale) }}</label>

            <div class="embed-financial-supports-search-input">
                <input type="text" :placeholder="$t('Suchbegriff', locale)" v-model="term"
                       :class="{'has-value': term}"
                       :id="`${$env.INSTANCE_ID}-financial-supports-search`"
                       @change="changeSearchTerm()"
                       @keyup="$event.keyCode === 13 ? changeSearchTerm() : null">
                <button class="embed-financial-supports-search-input-icon"
                        @click.stop="term = null; changeSearchTerm()"></button>
            </div>

        </div>

        <div class="embed-financial-supports-filters">

            <div class="embed-financial-supports-filters-select" data-filter-type="projectTypes">

                <button class="embed-financial-supports-filters-select-label"
                        :aria-expanded="activeFilterSelect === 'projectType' ? 'true' : 'false'"
                        :aria-controls="`${$env.INSTANCE_ID}-filter-projectType`"
                        @click.stop="clickFilterSelect('projectType')">
                    {{ $t('Projekttyp', locale) }}
                    <span class="embed-financial-supports-filters-select-label-count"
                          v-if="filters.find(f => f.type === 'projectType')">{{ filters.filter(f => f.type === 'projectType').length }}</span>
                </button>

                <div class="embed-financial-supports-filters-select-icon"
                     :class="{'is-active': activeFilterSelect === 'projectType'}"></div>

                <transition name="embed-financial-supports-filters-select-options" mode="out-in">

                    <div class="embed-financial-supports-filters-select-options"
                         :id="`${$env.INSTANCE_ID}-filter-projectType`"
                         role="group"
                         :aria-label="$t('Projekttyp', locale)"
                         v-if="activeFilterSelect === 'projectType'">

                        <button class="embed-financial-supports-filters-select-options-item"
                                v-for="projectType in projectTypes"
                                :class="{ 'is-selected': isFilterSelected({ type: 'projectType', entity: projectType }) }"
                                :aria-pressed="isFilterSelected({ type: 'projectType', entity: projectType }) ? 'true' : 'false'"
                                @click.stop="clickToggleFilter({ type: 'projectType', entity: projectType })">
                            {{ translateField(projectType, 'name', locale) }}
                        </button>

                    </div>

                </transition>

            </div>

            <div class="embed-financial-supports-filters-select" data-filter-type="beneficiaries">

                <button class="embed-financial-supports-filters-select-label"
                        :aria-expanded="activeFilterSelect === 'beneficiary' ? 'true' : 'false'"
                        :aria-controls="`${$env.INSTANCE_ID}-filter-beneficiary`"
                        @click.stop="clickFilterSelect('beneficiary')">
                    {{ $t('Begünstigte', locale) }}
                    <span class="embed-financial-supports-filters-select-label-count"
                          v-if="filters.find(f => f.type === 'beneficiary')">{{ filters.filter(f => f.type === 'beneficiary').length }}</span>
                </button>

                <div class="embed-financial-supports-filters-select-icon"
                     :class="{'is-active': activeFilterSelect === 'beneficiary'}"></div>

                <transition name="embed-financial-supports-filters-select-options" mode="out-in">

                    <div class="embed-financial-supports-filters-select-options"
                         :id="`${$env.INSTANCE_ID}-filter-beneficiary`"
                         role="group"
                         :aria-label="$t('Begünstigte', locale)"
                         v-if="activeFilterSelect === 'beneficiary'">

                        <button class="embed-financial-supports-filters-select-options-item"
                                v-for="beneficiary in beneficiaries"
                                :class="{ 'is-selected': isFilterSelected({ type: 'beneficiary', entity: beneficiary }) }"
                                :aria-pressed="isFilterSelected({ type: 'beneficiary', entity: beneficiary }) ? 'true' : 'false'"
                                @click.stop="clickToggleFilter({ type: 'beneficiary', entity: beneficiary })">
                            {{ translateField(beneficiary, 'name', locale) }}
                        </button>

                    </div>

                </transition>

            </div>

            <div class="embed-financial-supports-filters-select" data-filter-type="topics">

                <button class="embed-financial-supports-filters-select-label"
                        :aria-expanded="activeFilterSelect === 'topic' ? 'true' : 'false'"
                        :aria-controls="`${$env.INSTANCE_ID}-filter-topic`"
                        @click.stop="clickFilterSelect('topic')">
                    {{ $t('Thema', locale) }}
                    <span class="embed-financial-supports-filters-select-label-count"
                         v-if="filters.find(f => f.type === 'topic')">{{ filters.filter(f => f.type === 'topic').length }}</span>
                </button>

                <div class="embed-financial-supports-filters-select-icon"
                     :class="{'is-active': activeFilterSelect === 'topic'}"></div>

                <transition name="embed-financial-supports-filters-select-options" mode="out-in">

                    <div class="embed-financial-supports-filters-select-options"
                         :id="`${$env.INSTANCE_ID}-filter-topic`"
                         role="group"
                         :aria-label="$t('Thema', locale)"
                         v-if="activeFilterSelect === 'topic'">

                        <button class="embed-financial-supports-filters-select-options-item"
                                v-for="topic in topics"
                                :class="{ 'is-selected': isFilterSelected({ type: 'topic', entity: topic }) }"
                                :aria-pressed="isFilterSelected({ type: 'topic', entity: topic }) ? 'true' : 'false'"
                                @click.stop="clickToggleFilter({ type: 'topic', entity: topic })">
                            {{ translateField(topic, 'name', locale) }}
                        </button>

                    </div>

                </transition>

            </div>

            <div class="embed-financial-supports-filters-select" data-filter-type="instruments">

                <button class="embed-financial-supports-filters-select-label"
                        :aria-expanded="activeFilterSelect === 'instrument' ? 'true' : 'false'"
                        :aria-controls="`${$env.INSTANCE_ID}-filter-instrument`"
                        @click.stop="clickFilterSelect('instrument')">
                    {{ $t('Unterstützungsarten', locale) }}
                    <span class="embed-financial-supports-filters-select-label-count"
                          v-if="filters.find(f => f.type === 'instrument')">{{ filters.filter(f => f.type === 'instrument').length }}</span>
                </button>

                <div class="embed-financial-supports-filters-select-icon"
                     :class="{'is-active': activeFilterSelect === 'instrument'}"></div>

                <transition name="embed-financial-supports-filters-select-options" mode="out-in">

                    <div class="embed-financial-supports-filters-select-options"
                         :id="`${$env.INSTANCE_ID}-filter-instrument`"
                         role="group"
                         :aria-label="$t('Unterstützungsarten', locale)"
                         v-if="activeFilterSelect === 'instrument'">

                        <button class="embed-financial-supports-filters-select-options-item"
                                v-for="instrument in instruments"
                                :class="{ 'is-selected': isFilterSelected({ type: 'instrument', entity: instrument }) }"
                                :aria-pressed="isFilterSelected({ type: 'instrument', entity: instrument }) ? 'true' : 'false'"
                                @click.stop="clickToggleFilter({ type: 'instrument', entity: instrument })">
                            {{ translateField(instrument, 'name', locale) }}
                        </button>

                    </div>

                </transition>

            </div>

            <div class="embed-financial-supports-filters-select" data-filter-type="geographicRegions">

                <button class="embed-financial-supports-filters-select-label"
                        :aria-expanded="activeFilterSelect === 'geographicRegion' ? 'true' : 'false'"
                        :aria-controls="`${$env.INSTANCE_ID}-filter-geographicRegion`"
                        @click.stop="clickFilterSelect('geographicRegion')">
                    {{ $t('Geographische Region', locale) }}
                    <span class="embed-financial-supports-filters-select-label-count"
                          v-if="filters.find(f => f.type === 'geographicRegion')">{{ filters.filter(f => f.type === 'geographicRegion').length }}</span>
                </button>

                <div class="embed-financial-supports-filters-select-icon"
                     :class="{'is-active': activeFilterSelect === 'geographicRegion'}"></div>

                <transition name="embed-financial-supports-filters-select-options" mode="out-in">

                    <div class="embed-financial-supports-filters-select-options"
                         :id="`${$env.INSTANCE_ID}-filter-geographicRegion`"
                         role="group"
                         :aria-label="$t('Geographische Region', locale)"
                         v-if="activeFilterSelect === 'geographicRegion'">

                        <button class="embed-financial-supports-filters-select-options-item"
                                v-for="geographicRegion in geographicRegions"
                                :class="{ 'is-selected': isFilterSelected({ type: 'geographicRegion', entity: geographicRegion }) }"
                                :aria-pressed="isFilterSelected({ type: 'geographicRegion', entity: geographicRegion }) ? 'true' : 'false'"
                                @click.stop="clickToggleFilter({ type: 'geographicRegion', entity: geographicRegion })">
                            {{ translateField(geographicRegion, 'name', locale) }}
                        </button>

                    </div>

                </transition>

            </div>

            <div class="embed-financial-supports-filters-select" data-filter-type="states">

                <button class="embed-financial-supports-filters-select-label"
                        :aria-expanded="activeFilterSelect === 'state' ? 'true' : 'false'"
                        :aria-controls="`${$env.INSTANCE_ID}-filter-state`"
                        @click.stop="clickFilterSelect('state')">
                    {{ $t('Kantonale Finanzhilfen', locale) }}
                    <span class="embed-financial-supports-filters-select-label-count"
                          v-if="filters.find(f => f.type === 'state')">{{ filters.filter(f => f.type === 'state').length }}</span>
                </button>

                <div class="embed-financial-supports-filters-select-icon"
                     :class="{'is-active': activeFilterSelect === 'state'}"></div>

                <transition name="embed-financial-supports-filters-select-options" mode="out-in">

                    <div class="embed-financial-supports-filters-select-options"
                         :id="`${$env.INSTANCE_ID}-filter-state`"
                         role="group"
                         :aria-label="$t('Kantonale Finanzhilfen', locale)"
                         v-if="activeFilterSelect === 'state'">

                        <button class="embed-financial-supports-filters-select-options-item"
                                v-for="state in states"
                                :class="{ 'is-selected': isFilterSelected({ type: 'state', entity: state }) }"
                                :aria-pressed="isFilterSelected({ type: 'state', entity: state }) ? 'true' : 'false'"
                                @click.stop="clickToggleFilter({ type: 'state', entity: state })">
                            {{ translateField(state, 'name', locale) }}
                        </button>

                    </div>

                </transition>

            </div>

            <div class="embed-financial-supports-filters-list">

                <button class="embed-financial-supports-filters-list-item"
                        v-for="filter in filters"
                        :data-filter-type="filter.type"
                        :aria-label="`${$t('Filter entfernen', locale)}: ${translateField(filter.entity, 'name', locale)}`"
                        @click.stop="clickToggleFilter(filter)">{{ translateField(filter.entity, 'name', locale) }}</button>

            </div>

        </div>

        <transition name="embed-financial-supports-list" mode="out-in">

            <div class="embed-financial-supports-list" v-if="!isLoading">

                <button class="embed-financial-supports-list-item"
                        v-for="financialSupport in financialSupports" :id="'financialSupport-'+financialSupport.id"
                        :class="{'is-draft': financialSupport.isPublic !== true}"
                        :aria-label="translateField(financialSupport, 'name', locale)"
                        @click.stop="clickShowFinancialSupport(financialSupport)">

                    <span class="embed-financial-supports-list-item-content">

                        <h3 class="embed-financial-supports-list-item-content-title">
                            {{ translateField(financialSupport, 'name', locale) }}
                        </h3>

                        <p class="embed-financial-supports-list-item-content-description">
                            {{ $helpers.textExcerpt(stripHtml(translateField(financialSupport, 'description', locale)), 173) }}
                        </p>

                        <span class="embed-financial-supports-list-item-content-tags">

                            <span class="embed-financial-supports-list-item-content-tags-item"
                                  v-for="topic in financialSupport.topics.map(e => getTopicById(e.id)).filter(e => e).filter(e => topics.find(t => t.id === e.id))">
                                <template v-if="topic.icon">
                                    <div class="embed-financial-supports-list-item-content-tags-item-icon"
                                         v-html="topic.icon">
                                    </div>
                                </template>
                                {{ translateField(topic, 'name', locale) }}
                            </span>

                        </span>

                    </span>

                </button>

            </div>

        </transition>

        <transition name="embed-financial-supports-overlay" mode="out-in">

            <div class="embed-financial-supports-overlay" 
                 v-if="financialSupport" 
                 @click="clickHideFinancialSupport()"
                 role="dialog"
                 aria-modal="true"
                 ref="financialSupportDialog"
                 tabindex="-1"
                 :aria-label="translateField(financialSupport, 'name', locale)">

                <EmbedFinancialSupportsView :financialSupport="financialSupport" :locale="locale" @click.stop
                                   @clickClose="clickHideFinancialSupport()"></EmbedFinancialSupportsView>

            </div>

        </transition>

    </div>

</template>

<script>

import {mapGetters, mapState} from 'vuex';
import { translateField } from '../utils/filters';
import EmbedFinancialSupportsView from './EmbedFinancialSupportsView';
import {track, trackDevice, trackPageView} from '../utils/logger';

export default {

    components: {
        EmbedFinancialSupportsView,
    },

    data() {
        return {
            isLoading: false,
            financialSupports: [],
            term: '',
            filters: [],
            activeFilterSelect: null,
            financialSupport: null,
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
            projectTypes: function (state) {
                return state.projectTypes.all
                    .filter(e => !e.context || e.context === 'financial-support')
                    .map(this.$clientOptions?.middleware?.mapProjectTypes || (e => e))
                    .filter(this.$clientOptions?.middleware?.filterProjectTypes || (e => e.isPublic))
                    .sort(this.$clientOptions?.middleware?.sortProjectTypes || ((a, b) => a.position - b.position));
            },
            beneficiaries: function (state) {
                return state.beneficiaries.all
                    .filter(e => !e.context || e.context === 'financial-support')
                    .map(this.$clientOptions?.middleware?.mapBeneficiaries || (e => e))
                    .filter(this.$clientOptions?.middleware?.filterBeneficiaries  || (e => e.isPublic))
                    .sort(this.$clientOptions?.middleware?.sortBeneficiaries  || ((a, b) => a.position - b.position));
            },
            topics: function (state) {
                return state.topics.all
                    .filter(e => !e.context || e.context === 'financial-support')
                    .map(this.$clientOptions?.middleware?.mapTopics || (e => e))
                    .filter(this.$clientOptions?.middleware?.filterTopics || (e => e.isPublic))
                    .sort(this.$clientOptions?.middleware?.sortTopics || ((a, b) => a.position - b.position));
            },
            instruments: function (state) {
                return state.instruments.all
                    .filter(e => !e.context || e.context === 'financial-support')
                    .map(this.$clientOptions?.middleware?.mapInstruments || (e => e))
                    .filter(this.$clientOptions?.middleware?.filterInstruments || (e => e.isPublic))
                    .sort(this.$clientOptions?.middleware?.sortInstruments || ((a, b) => a.position - b.position));
            },
            geographicRegions: function (state) {
                return state.geographicRegions.all
                    .filter(e => !e.context || e.context === 'financial-support')
                    .map(this.$clientOptions?.middleware?.mapGeographicRegions || (e => e))
                    .filter(this.$clientOptions?.middleware?.filterGeographicRegions || (e => e.isPublic))
                    .sort(this.$clientOptions?.middleware?.sortGeographicRegions || ((a, b) => a.position - b.position));
            },
            states: function (state) {
                return state.states.all
                    .filter(e => !e.context || e.context === 'financial-support')
                    .map(this.$clientOptions?.middleware?.mapStates || (e => e))
                    .filter(this.$clientOptions?.middleware?.filterStates || (e => e.isPublic))
                    .sort(this.$clientOptions?.middleware?.sortStates || ((a, b) => a.name?.localeCompare(b.name || '')));
            },
        }),
        ...mapGetters({
            getAuthoritiesById: 'authorities/getById',
            getProjectTypeById: 'projectTypes/getById',
            getBeneficiaryById: 'beneficiaries/getById',
            getTopicById: 'topics/getById',
            getInstrumentById: 'instruments/getById',
            getGeographicRegionById: 'geographicRegions/getById',
            getStateById: 'states/getById',
        }),
    },

    methods: {

        translateField,

        keyUp (event) {

            if(event.key === 'Escape') {
                this.activeFilterSelect = null;
                if(this.financialSupport) {
                    this.clickHideFinancialSupport();
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

            return params;

        },

        clickFilterSelect(name) {

            if(this.activeFilterSelect === name) {

                if(!this.disableTelemetry) {
                    track('FinancialSupport Filter', 'Hide', name);
                }

                return this.activeFilterSelect = null;
            }

            if(!this.disableTelemetry) {
                track('FinancialSupport Filter', 'Show', name);
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
                    track('FinancialSupport Filter', 'Disable', {
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
                track('FinancialSupport Filter', 'Enable', {
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
                track('FinancialSupport Search', 'Change', this.term);
            }

        },

        clickShowFinancialSupport(financialSupport) {

            this.lastFocusedElement = document.activeElement;

            if(this.history) {
                window.history.pushState(null, null, this.getHistoryQueryString(financialSupport));
            }

            if(!this.disableTelemetry) {
                track('FinancialSupport Navigation', 'Show FinancialSupport', {
                    id: financialSupport.id,
                    name: translateField(financialSupport, 'name', this.locale),
                });
            }

            this.financialSupport = financialSupport;

            this.$nextTick(() => {
                if(this.$refs.financialSupportDialog) {
                    this.$refs.financialSupportDialog.focus();
                }
            });

        },

        clickHideFinancialSupport() {

            if(this.history) {
                window.history.pushState(null, null, this.getHistoryQueryString());
            }

            if(!this.disableTelemetry) {
                track('FinancialSupport Navigation', 'Hide FinancialSupport', {
                    id: this.financialSupport.id,
                    name: translateField(this.financialSupport, 'name', this.locale),
                });
            }

            this.financialSupport = null;

            this.$nextTick(() => {
                if(this.lastFocusedElement && typeof this.lastFocusedElement.focus === 'function') {
                    this.lastFocusedElement.focus();
                }
            });

        },

        popState(event) {

            this.financialSupport = null;

            if(this.getUrlParams()['financial-support-id']) {
                this.$store.dispatch('financialSupports/load', this.getUrlParams()['financial-support-id']).then((financialSupport) => {
                    this.financialSupport = financialSupport;
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

                if(!['projectTypes', 'beneficiaries', 'topics', 'instruments', 'geographicRegions', 'states'].includes(k)) {
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

        getHistoryQueryString(financialSupport) {

            let result = [];

            if(financialSupport) {
                result.push('financial-support-id='+financialSupport.id+'&name='+encodeURIComponent(translateField(financialSupport, 'name', this.locale)));
            }

            if(this.term) {
                result.push('term='+encodeURIComponent(this.term));
            }

            for(let filter of this.filters) {
                let collection = filter.type + 's';

                if(filter.type === 'beneficiary') {
                    collection = 'beneficiaries';
                }

                result.push(collection+'[]='+encodeURIComponent(translateField(filter.entity, 'name', this.locale)));
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

            ['projectType', 'beneficiary', 'topic', 'instrument', 'geographicRegion', 'state'].forEach((key) => {

                let collection = key+'s';

                if(key === 'beneficiary') {
                    collection = 'beneficiaries';
                }

                if(!this.getUrlParams()[collection]) {
                    return;
                }

                this.getUrlParams()[collection].forEach((f) => {
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

        stripHtml (html) {
            let tmp = document.createElement('div');
            tmp.innerHTML = html;
            return tmp.textContent || tmp.innerText || '';
        },

        reload() {
            this.isLoading = true;

            return this.$store.dispatch('financialSupports/loadFiltered', this.getFilterParams()).then((financialSupports) => {

                this.financialSupports = [
                    ...financialSupports,
                ];

                this.isLoading = false;

            });
        },

    },

    created() {
        this.filters = this.$clientOptions?.defaultFilters || [];

        if(this.history && this.getUrlParams()['term']) {
            this.term = this.getUrlParams()['term'];
        }

        this.reload();

        Promise.all([
            this.$store.dispatch('authorities/loadAll'),
            this.$store.dispatch('projectTypes/loadAll'),
            this.$store.dispatch('beneficiaries/loadAll'),
            this.$store.dispatch('topics/loadAll'),
            this.$store.dispatch('instruments/loadAll'),
            this.$store.dispatch('geographicRegions/loadAll'),
            this.$store.dispatch('states/loadAll'),
        ]).then(() => {

            this.filters = this.filters
                .filter((filter) => {
                    return ['projectType', 'beneficiary', 'topic', 'instrument', 'geographicRegion', 'state'].includes(filter.type);
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

        if(this.history && this.getUrlParams()['financial-support-id']) {
            this.$store.dispatch('financialSupports/load', this.getUrlParams()['financial-support-id']).then((financialSupport) => {
                this.financialSupport = financialSupport;
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
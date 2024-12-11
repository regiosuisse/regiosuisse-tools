<template>

    <div class="embed-jobs" :class="[$env.INSTANCE_ID+'-jobs', {'is-responsive': responsive}]" @click.stop="clickInside">

        <div class="embed-jobs-search">

            <div class="embed-jobs-search-input">
                <input type="text" :placeholder="$t('Suchbegriff', locale)" v-model="term"
                       :class="{'has-value': term}"
                       @change="changeSearchTerm()"
                       @keyup="$event.keyCode === 13 ? changeSearchTerm() : null">
                <div class="embed-jobs-search-input-icon" @click.stop="term = null; changeSearchTerm()"></div>
            </div>

        </div>

        <div class="embed-jobs-filters">

            <div class="embed-jobs-filters-select" data-filter-type="stints">

                <div class="embed-jobs-filters-select-label"
                     @click.stop="clickFilterSelect('stint')">{{ $t('Pensum', locale) }}</div>

                <div class="embed-jobs-filters-select-icon"
                     :class="{'is-active': activeFilterSelect === 'stint'}"></div>

                <transition name="embed-jobs-filters-select-options" mode="out-in">

                    <div class="embed-jobs-filters-select-options" v-if="activeFilterSelect === 'stint'">

                        <div class="embed-jobs-filters-select-options-item"
                             v-for="stint in stints"
                             :class="{ 'is-selected': isFilterSelected({ type: 'stint', entity: stint }) }"
                             @click.stop="clickToggleFilter({ type: 'stint', entity: stint })">
                            {{ translateField(stint, 'name', locale) }}
                        </div>

                    </div>

                </transition>

            </div>

            <div class="embed-jobs-filters-select" data-filter-type="locations">

                <div class="embed-jobs-filters-select-label"
                     @click.stop="clickFilterSelect('location')">{{ $t('Ort', locale) }}</div>

                <div class="embed-jobs-filters-select-icon"
                     :class="{'is-active': activeFilterSelect === 'location'}"></div>

                <transition name="embed-jobs-filters-select-options" mode="out-in">

                    <div class="embed-jobs-filters-select-options" v-if="activeFilterSelect === 'location'">

                        <div class="embed-jobs-filters-select-options-item"
                             v-for="location in locations"
                             :class="{ 'is-selected': isFilterSelected({ type: 'location', entity: location }) }"
                             @click.stop="clickToggleFilter({ type: 'location', entity: location })">
                            {{ translateField(location, 'name', locale) }}
                        </div>

                    </div>

                </transition>

            </div>

            <div class="embed-jobs-filters-list">

                <div class="embed-jobs-filters-list-item"
                     v-for="filter in filters"
                     @click.stop="clickToggleFilter(filter)">{{ translateField(filter.entity, 'name', locale) }}</div>

            </div>

        </div>

        <div class="embed-jobs-filterbar">
            <button class="button primary add-job-button" @click="showJobModal = true">Stellenausschreibung Hinzufügen</button>
        </div>

        <transition name="embed-jobs-list" mode="out-in">

            <div class="embed-jobs-list" v-if="!isLoading">

                <div class="embed-jobs-list-item"
                     v-for="job in jobs" :id="'job-'+job.id"
                     :class="{'is-draft': job.isPublic !== true}"
                     @click.stop="clickShowJob(job)">

                    <div class="embed-jobs-list-item-header" v-if="!disableThumbnails">

                        <div class="embed-jobs-list-item-header-image" v-if="job.images.length" :style="{
                            backgroundImage: 'url('+$env.HOST+'/api/v1/files/view/'+ job.images[0].id +'.' + job.images[0].extension+')'
                        }"></div>

                        <div class="embed-jobs-list-item-header-image" v-else></div>

                    </div>

                    <div class="embed-jobs-list-item-content">

                        <h3 class="embed-jobs-list-item-content-title">
                            {{ translateField(job, 'name', locale) }}
                        </h3>

                        <p class="embed-jobs-list-item-content-description">
                            {{ translateField(job, 'employer', locale) }}
                        </p>

                        <div class="embed-jobs-list-item-content-tags">

                            <div class="embed-jobs-list-item-content-tags-item"
                                 v-for="stint in job.stints.filter(e => getStintById(e.id))">
                                {{ translateField(getStintById(stint.id), 'name', locale) }}
                            </div>

                            <div class="embed-jobs-list-item-content-tags-item"
                                 v-for="location in job.locations.filter(e => getLocationById(e.id))">
                                {{ translateField(getLocationById(location.id), 'name', locale) }}
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </transition>

        <transition name="embed-jobs-overlay" mode="out-in">

            <div class="embed-jobs-overlay" v-if="job" @click="clickHideJob()">

                <EmbedJobsView :job="job" :locale="locale" @click.stop
                                   @clickClose="clickHideJob()"></EmbedJobsView>

            </div>

        </transition>

        <JobModal v-if="showJobModal" @close="showJobModal = false">
            <div class="job-form-modal">
                <h3 class="modal-title">Neuen Job hinzufügen</h3>
                <form @submit.prevent="submitJob" class="job-form">
                    <div class="job-form-columns">
                        <div class="job-form-column">
                            <div class="form-group">
                                <label for="jobTitle">Bezeichnung</label>
                                <input id="jobTitle" class="form-control" v-model="newJob.title" required />
                            </div>
                            <div class="form-group">
                                <label for="jobLocation">Arbeitsort</label>
                                <tag-selector id="jobLocation" 
                                    :model="newJob.locations"
                                    :options="locations.filter(location => !location.context || location.context === 'job')" 
                                    :searchType="'select'"
                                    required>
                                </tag-selector>
                            </div>
                            <div class="form-group">
                                <label for="jobStint">Pensum</label>
                                <tag-selector id="jobStint" 
                                    :model="newJob.stints"
                                    :options="stints.filter(stint => !stint.context || stint.context === 'job')" 
                                    :searchType="'select'"
                                    required>
                                </tag-selector>
                            </div>
                            <div class="form-group">
                                <label for="jobDescription">Beschreibung</label>
                                <textarea id="jobDescription" class="form-control" rows="5" v-model="newJob.description" required></textarea>
                            </div>
                        </div>
                        <div class="job-form-column">
                            <div class="form-group">
                                <label for="jobEmployer">Arbeitgeber</label>
                                <input id="jobEmployer" class="form-control" v-model="newJob.employer" required />
                            </div>
                            <div class="form-group">
                                <label for="jobEmployerLocation">Arbeitgeber Location</label>
                                <input id="jobEmployerLocation" class="form-control" v-model="newJob.location" />
                            </div>
                            <div class="form-group">
                                <label for="jobContact">Kontakt</label>
                                <input id="jobContact" class="form-control" v-model="newJob.contact" required />
                            </div>
                            <div class="form-group">
                                <label for="jobDeadline">Bewerbungsfrist</label>
                                <input type="date" id="jobDeadline" class="form-control" v-model="newJob.applicationDeadline" required />
                            </div>
                        </div>
                    </div>
                    
                    <!-- Links and Documents Section -->
                    <div class="additional-sections">
                        <div class="form-group">
                            <label>Links</label>
                            <div class="links-list">
                                <div v-for="(link, index) in newJob.links" :key="index" class="link-item">
                                    <input type="text" class="form-control" v-model="link.label" placeholder="Bezeichnung">
                                    <input type="text" class="form-control" v-model="link.value" placeholder="URL">
                                    <button type="button" class="button error" @click="removeLink(index)">Entfernen</button>
                                </div>
                            </div>
                            <button type="button" class="button success" @click="addLink">Link hinzufügen</button>
                        </div>
                        
                        <div class="form-group">
                            <label>Dokumente</label>
                            <file-selector :items="newJob.files" @changed="updateFiles"></file-selector>
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="button warning" @click="showJobModal = false">Abbrechen</button>
                        <button type="submit" class="button primary">Speichern</button>
                    </div>
                </form>
            </div>
        </JobModal>

    </div>

</template>

<script>

import {mapGetters, mapState} from 'vuex';
import { translateField } from '../utils/filters';
import EmbedJobsView from './EmbedJobsView';
import {track, trackDevice, trackPageView} from '../utils/logger';
import JobModal from './JobModal.vue';
import TagSelector from './TagSelector';
import FileSelector from './FileSelector';

export default {

    components: {
        EmbedJobsView,
        JobModal,
        TagSelector,
        FileSelector,
    },

    data() {
        return {
            isLoading: false,
            jobs: [],
            term: '',
            filters: [],
            activeFilterSelect: null,
            job: null,
            showJobModal: false,
            newJob: {
                title: '',
                locations: [],
                stints: [],
                description: '',
                employer: '',
                location: '',
                contact: '',
                applicationDeadline: '',
                links: [],
                files: [],
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
            locations: function (state) {
                return state.locations.all
                    .filter(e => !e.context || e.context === 'job')
                    .map(this.$clientOptions?.middleware?.mapLocations || (e => e))
                    .filter(this.$clientOptions?.middleware?.filterLocations  || (e => e.isPublic))
                    .sort(this.$clientOptions?.middleware?.sortLocations  || ((a, b) => a.position - b.position));
            },
            stints: function (state) {
                return state.stints.all
                    .filter(e => !e.context || e.context === 'job')
                    .map(this.$clientOptions?.middleware?.mapStints || (e => e))
                    .filter(this.$clientOptions?.middleware?.filterStints  || (e => e.isPublic))
                    .sort(this.$clientOptions?.middleware?.sortStints  || ((a, b) => a.position - b.position));
            },
        }),
        ...mapGetters({
            getLocationById: 'locations/getById',
            getStintById: 'stints/getById',
        }),
    },

    methods: {

        translateField,

        keyUp (event) {

            if(event.keyCode === 27) {
                this.activeFilterSelect = null;
                this.job = null;
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

            params.archive = 0;

            return params;

        },

        clickFilterSelect(name) {

            if(this.activeFilterSelect === name) {

                if(!this.disableTelemetry) {
                    track('Job Filter', 'Hide', name);
                }

                return this.activeFilterSelect = null;
            }

            if(!this.disableTelemetry) {
                track('Job Filter', 'Show', name);
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
                    track('Job Filter', 'Disable', {
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
                track('Job Filter', 'Enable', {
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
                track('Job Search', 'Change', this.term);
            }

        },

        clickShowJob(job) {

            if(this.history) {
                window.history.pushState(null, null, this.getHistoryQueryString(job));
            }

            if(!this.disableTelemetry) {
                track('Job Navigation', 'Show Job', {
                    id: job.id,
                    name: translateField(job, 'name', this.locale),
                });
            }

            this.job = job;

        },

        clickHideJob() {

            if(this.history) {
                window.history.pushState(null, null, this.getHistoryQueryString());
            }

            if(!this.disableTelemetry) {
                track('Job Navigation', 'Hide Job', {
                    id: this.job.id,
                    name: translateField(this.job, 'name', this.locale),
                });
            }

            this.job = null;

        },

        popState(event) {

            this.job = null;

            if(this.getUrlParams()['job-id']) {
                this.$store.dispatch('jobs/load', this.getUrlParams()['job-id']).then((job) => {
                    this.job = job;
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

                if(!['locations', 'stints'].includes(k)) {
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

        getHistoryQueryString(job) {

            let result = [];

            if(job) {
                result.push('job-id='+job.id+'&name='+encodeURIComponent(translateField(job, 'name', this.locale)));
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

            ['location', 'stint'].forEach((key) => {

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

            return this.$store.dispatch('jobs/loadFiltered', this.getFilterParams()).then((jobs) => {

                this.jobs = [
                    ...jobs,
                ];

                this.isLoading = false;

            });
        },

        submitJob() {
            // Prepare the job data
            const jobData = {
                title: this.newJob.title,
                description: this.newJob.description,
                employer: this.newJob.employer,
                location: this.newJob.location,
                contact: this.newJob.contact,
                applicationDeadline: this.newJob.applicationDeadline,
                // Send arrays of locations and stints
                locations: this.newJob.locations.map(loc => ({ id: loc.id })),
                stints: this.newJob.stints.map(stint => ({ id: stint.id })),
                // Add links and files
                links: this.newJob.links || [],
                files: this.newJob.files || []
            };

            this.$store.dispatch('jobs/createFromEmbed', jobData)
                .then(() => {
                    this.showJobModal = false;
                    // Reset form
                    this.newJob = {
                        title: '',
                        locations: [],
                        stints: [],
                        description: '',
                        employer: '',
                        location: '',
                        contact: '',
                        applicationDeadline: '',
                        links: [],
                        files: [],
                    };
                })
                .catch(error => {
                    console.error('Error creating job:', error);
                    // Show error modal
                    this.modal = {
                        type: 'error',
                        message: 'Fehler beim Erstellen des Jobs. Bitte überprüfen Sie alle Pflichtfelder.'
                    };
                });
        },

        addLink() {
            this.newJob.links.push({
                label: '',
                value: ''
            });
        },

        removeLink(index) {
            this.newJob.links.splice(index, 1);
        },

        updateFiles(files) {
            this.newJob.files = files;
        },

    },

    created() {
        this.filters = this.$clientOptions?.defaultFilters || [];

        if(this.history && this.getUrlParams()['term']) {
            this.term = this.getUrlParams()['term'];
        }

        this.reload();

        Promise.all([
            this.$store.dispatch('locations/loadAll'),
            this.$store.dispatch('stints/loadAll'),
        ]).then(() => {

            this.filters = this.filters
                .filter((filter) => {
                    return ['location', 'stint'].includes(filter.type);
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

        if(this.history && this.getUrlParams()['job-id']) {
            this.$store.dispatch('jobs/load', this.getUrlParams()['job-id']).then((job) => {
                this.job = job;
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
.embed-jobs-filterbar {
    padding: 1rem;
    text-align: end;
    border-bottom: 1px solid #e5e5e5;
    margin-bottom: 1rem;
    margin: 0 auto;
    max-width: calc(var(--regiosuisse-max-width) + var(--regiosuisse-gutter-width));

    .add-job-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 200px;

    }
}

.job-form-modal {
    background: white;
    padding: 2rem;
    border-radius: 4px;
    max-width: 600px;
    width: 100%;
    margin: 0 auto;

    .modal-title {
        text-align: center;
        margin-bottom: 2rem;
        font-size: 1.5rem;
        font-weight: bold;
    }

    .job-form {
        &-columns {
            display: flex;
            gap: 2rem;
        }

        &-column {
            flex: 1;
            min-width: 0;
        }

        .links-list {
            margin-bottom: 1rem;

            .link-item {
                display: grid;
                grid-template-columns: 1fr 1fr auto;
                gap: 0.5rem;
                margin-bottom: 0.5rem;
                align-items: start;

                .button {
                    padding: 0.5rem;
                    height: 100%;
                }
            }
        }

        .form-group {
            margin-bottom: 1.5rem;

            label {
                display: block;
                margin-bottom: 0.5rem;
            }

            .form-control {
                width: 100%;
                padding: 0.5rem;
                border: 1px solid #e5e5e5;
                border-radius: 4px;

                &:focus {
                    outline: none;
                    border-color: #666;
                }
            }

            textarea.form-control {
                min-height: 100px;
                resize: vertical;
            }

            button.success {
                margin-top: 0.5rem;
            }
        }
    }

    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 2rem;

        .button {
            min-width: 120px;
            justify-content: center;
        }
    }
}

.additional-sections {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #eee;
}

.links-list {
    margin-bottom: 1rem;
    
    .link-item {
        display: flex;
        gap: 1rem;
        margin-bottom: 0.5rem;
        
        input {
            flex: 1;
        }
        
        button {
            flex-shrink: 0;
        }
    }
}
</style>
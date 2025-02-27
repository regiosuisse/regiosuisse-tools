<template>
    <div class="embed-events" :class="[$env.INSTANCE_ID+'-events', {'is-responsive': responsive}]" @click.stop="clickInside">
        <div class="embed-events-search">
            <div class="embed-events-search-input">
                <input type="text" :placeholder="$t('Suchbegriff', locale)" v-model="term"
                       :class="{'has-value': term}"
                       @change="changeSearchTerm()"
                       @keyup="$event.keyCode === 13 ? changeSearchTerm() : null">
                <div class="embed-events-search-input-icon" @click.stop="term = null; changeSearchTerm()"></div>
            </div>
        </div>

        <div class="embed-events-filters">
            <div class="embed-events-filters-toggle" data-filter-type="type"
                 @click="clickToggleType()" :class="{ 'is-active': type === 'regiosuisse' }">
                <div class="embed-events-filters-toggle-button"></div>
                <div class="embed-events-filters-toggle-label">{{ $t('Nur regiosuisse-Veranstaltungen anzeigen', locale) }}</div>
            </div>

            <div class="embed-events-filters-toggle" data-filter-type="archive"
                 @click="clickToggleArchive()" :class="{ 'is-active': archive }">
                <div class="embed-events-filters-toggle-button"></div>
                <div class="embed-events-filters-toggle-label">{{ $t('Vergangene Veranstaltungen anzeigen', locale) }}</div>
            </div>

            <div class="embed-events-filters-select" data-filter-type="topics">
                <div class="embed-events-filters-select-label"
                     @click.stop="clickFilterSelect('topic')">{{ $t('Thema', locale) }}</div>

                <div class="embed-events-filters-select-icon"
                     :class="{'is-active': activeFilterSelect === 'topic'}"></div>

                <transition name="embed-events-filters-select-options" mode="out-in">
                    <div class="embed-events-filters-select-options" v-if="activeFilterSelect === 'topic'">
                        <div class="embed-events-filters-select-options-item"
                             v-for="topic in topics"
                             :key="topic.id"
                             :class="{ 'is-selected': isFilterSelected({ type: 'topic', entity: topic }) }"
                             @click.stop="clickToggleFilter({ type: 'topic', entity: topic })">
                            {{ translateField(topic, 'name', locale) }}
                        </div>
                    </div>
                </transition>
            </div>

            <div class="embed-events-filters-select" data-filter-type="languages">
                <div class="embed-events-filters-select-label"
                     @click.stop="clickFilterSelect('language')">{{ $t('Durchführungssprache', locale) }}</div>

                <div class="embed-events-filters-select-icon"
                     :class="{'is-active': activeFilterSelect === 'language'}"></div>

                <transition name="embed-events-filters-select-options" mode="out-in">
                    <div class="embed-events-filters-select-options" v-if="activeFilterSelect === 'language'">
                        <div class="embed-events-filters-select-options-item"
                             v-for="language in languages"
                             :key="language.id"
                             :class="{ 'is-selected': isFilterSelected({ type: 'language', entity: language }) }"
                             @click.stop="clickToggleFilter({ type: 'language', entity: language })">
                            {{ translateField(language, 'name', locale) }}
                        </div>
                    </div>
                </transition>
            </div>

            <div class="embed-events-filters-select" data-filter-type="locations">
                <div class="embed-events-filters-select-label"
                     @click.stop="clickFilterSelect('location')">{{ $t('Durchführungsort', locale) }}</div>

                <div class="embed-events-filters-select-icon"
                     :class="{'is-active': activeFilterSelect === 'location'}"></div>

                <transition name="embed-events-filters-select-options" mode="out-in">
                    <div class="embed-events-filters-select-options" v-if="activeFilterSelect === 'location'">
                        <div class="embed-events-filters-select-options-item"
                             v-for="location in locations"
                             :key="location.id"
                             :class="{ 'is-selected': isFilterSelected({ type: 'location', entity: location }) }"
                             @click.stop="clickToggleFilter({ type: 'location', entity: location })">
                            {{ translateField(location, 'name', locale) }}
                        </div>
                    </div>
                </transition>
            </div>

            <div class="embed-events-filters-list">
                <div class="embed-events-filters-list-item"
                     v-for="(filter, fIndex) in filters"
                     :key="fIndex"
                     @click.stop="clickToggleFilter(filter)">{{ translateField(filter.entity, 'name', locale) }}</div>
            </div>
        </div>

        <!-- <div class="embed-events-filterbar">
            <button class="button primary add-event-button" @click="showEventModal = true">{{ $t('event.submit', locale) }}</button>
        </div> -->

        <transition name="embed-events-list" mode="out-in">
            <div class="embed-events-list" v-if="!isLoading">
                <div class="embed-events-list-item"
                     v-for="event in events" :id="'event-'+event.id"
                     :key="event.id"
                     :class="{'is-draft': event.isPublic !== true}"
                     @click.stop="clickShowEvent(event)">
                    <div class="embed-events-list-item-header">
                        <div class="embed-events-list-item-header-image" v-if="event.images.length" :style="{
                            backgroundImage: 'url('+$env.HOST+'/api/v1/files/view/'+ event.images[0].id +'.' + event.images[0].extension+')'
                        }"></div>
                        <div class="embed-events-list-item-header-image" v-else></div>
                    </div>
                    <div class="embed-events-list-item-content">
                        <h3 class="embed-events-list-item-content-title" :style="event.type !== 'external' && event.color ? 'color:'+event.color : null">
                            {{ translateField(event, 'title', locale) }}
                        </h3>
                        <h4 class="embed-events-list-item-content-subtitle" v-if="event.startDate && event.endDate">
                            {{ $helpers.formatDateRange(event.startDate, event.endDate) }}
                        </h4>
                        <p class="embed-events-list-item-content-description">
                            {{ $helpers.textExcerpt(translateField(event, 'description', locale) || $helpers.stripHTML(translateField(event, 'text', locale)), 168, '...') }}
                        </p>
                        <div class="embed-events-list-item-content-tags">
                            <div class="embed-events-list-item-content-tags-item"
                                 v-for="topic in event.topics.filter(e => getTopicById(e.id))"
                                 :key="'topic-'+topic.id">
                                {{ translateField(getTopicById(topic.id), 'name', locale) }}
                            </div>
                            <div class="embed-events-list-item-content-tags-item"
                                 v-for="language in event.languages.filter(e => getLanguageById(e.id))"
                                 :key="'language-'+language.id">
                                {{ translateField(getLanguageById(language.id), 'name', locale) }}
                            </div>
                            <div class="embed-events-list-item-content-tags-item"
                                 v-for="location in event.locations.filter(e => getLocationById(e.id))"
                                 :key="'location-'+location.id">
                                {{ translateField(getLocationById(location.id), 'name', locale) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

        <div class="embed-events-actions" v-if="!isLoading">
            <div class="embed-events-actions-item" v-if="!isLoadedFully">
                <a class="embed-events-button" @click="clickLoadMore()" v-if="!isLoadingMore">{{ $t('Mehr Einträge laden', locale) }}</a>
                <a class="embed-events-button is-disabled" v-else>{{ $t('Einträge werden geladen...', locale) }}</a>
            </div>
        </div>

        <transition name="embed-events-overlay" mode="out-in">
            <div class="embed-events-overlay" v-if="event" @click="clickHideEvent()">
                <EmbedEventsView :event="event" :locale="locale" @click.stop
                                   @clickClose="clickHideEvent()"></EmbedEventsView>
            </div>
        </transition>

        <EventModal v-if="showEventModal" @close="showEventModal = false">
            <div class="event-form-modal">
                <div class="event-form-header">
                    <img :src="$env.THEME_ICON" alt="regiosuisse Logo" class="regiosuisse-logo">
                    <h3 class="modal-title">{{ $t('event.submit.new', locale) }}</h3>
                    <p class="header-description">
                        {{ $t('event.submit.description', locale) }}
                    </p>
                </div>
                <form @submit.prevent="submitEvent" class="event-form">
                    <div class="event-form-columns">
                        <div class="event-form-column">
                            <div class="form-group">
                                <label for="eventTitle">{{ $t('event.title', locale) }}</label>
                                <small class="help-text">{{ $t('event.title.help', locale) }}</small>
                                <input id="eventTitle" class="form-control" v-model="newEvent.title" required />
                            </div>

                            <div class="form-group">
                                <label for="eventLocation">{{ $t('event.location', locale) }}</label>
                                <small class="help-text">{{ $t('event.location.help', locale) }}</small>
                                <tag-selector id="eventLocation" 
                                    :model="newEvent.locations"
                                    :options="locations.filter(location => !location.context || location.context === 'event')" 
                                    :searchType="'select'"
                                    required>
                                </tag-selector>
                            </div>

                            <div class="form-group">
                                <label for="eventTopics">{{ $t('event.topics', locale) }}</label>
                                <small class="help-text">{{ $t('event.topics.help', locale) }}</small>
                                <tag-selector id="eventTopics" 
                                    :model="newEvent.topics"
                                    :options="topics.filter(topic => !topic.context || topic.context === 'event')" 
                                    :searchType="'select'">
                                </tag-selector>
                            </div>

                            <div class="form-group">
                                <label for="eventLanguages">{{ $t('event.languages', locale) }}</label>
                                <small class="help-text">{{ $t('event.languages.help', locale) }}</small>
                                <tag-selector id="eventLanguages" 
                                    :model="newEvent.languages"
                                    :options="languages.filter(language => !language.context || language.context === 'event')" 
                                    :searchType="'select'">
                                </tag-selector>
                            </div>
                        </div>
                        
                        <div class="event-form-column">
                            <div class="form-group">
                                <label>{{ $t('event.contact', locale) }}</label>
                                <small class="help-text">{{ $t('event.contact.help', locale) }}</small>
                                <div class="contact-fields">
                                    <div class="form-group">
                                        <label for="contactName">{{ $t('event.contact.name', locale) }}</label>
                                        <input id="contactName" class="form-control" v-model="newEvent.contactInfo.name" />
                                    </div>
                                    <div class="form-group">
                                        <label for="contactEmail">{{ $t('event.contact.email', locale) }}</label>
                                        <input id="contactEmail" type="email" class="form-control" v-model="newEvent.contactInfo.email" required />
                                    </div>
                                    <div class="form-group">
                                        <label for="contactPhone">{{ $t('event.contact.phone', locale) }}</label>
                                        <input id="contactPhone" class="form-control" v-model="newEvent.contactInfo.phone" />
                                    </div>
                                    <div class="form-group">
                                        <label for="eventVenueLocation">{{ $t('event.venue_location', locale) }}</label>
                                        <small class="help-text">{{ $t('event.venue_location.help', locale) }}</small>
                                        <input id="eventVenueLocation" class="form-control" v-model="newEvent.location" />
                                    </div>
                                    <div class="form-group">
                                        <label for="eventOrganizer">{{ $t('event.organizer', locale) }}</label>
                                        <small class="help-text">{{ $t('event.organizer.help', locale) }}</small>
                                        <input id="eventOrganizer" class="form-control" v-model="newEvent.organizer" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="form-section-divider">

                    <!-- Registration link full width -->
                    <div class="form-group">
                        <label for="eventRegistration">{{ $t('event.registration', locale) }}</label>
                        <small class="help-text">{{ $t('event.registration.help', locale) }}</small>
                        <input id="eventRegistration" type="url" class="form-control" v-model="newEvent.registration" />
                    </div>

                    <!-- New row for date, description and links -->
                    <div class="event-form-section">
                        <div class="form-group">
                            <label>{{ $t('event.date', locale) }}</label>
                            <small class="help-text">{{ $t('event.date.help', locale) }}</small>
                            <div class="date-time-fields">
                                <div class="date-time-field">
                                    <label>{{ $t('event.start', locale) }}</label>
                                    <date-picker mode="dateTime" :is24hr="true" v-model="newEvent.startDate" :locale="'de'">
                                        <template v-slot="{ inputValue, inputEvents }">
                                            <input type="text" 
                                                   class="form-control"
                                                   :value="inputValue"
                                                   v-on="inputEvents">
                                        </template>
                                    </date-picker>
                                </div>
                                <div class="date-time-field">
                                    <label>{{ $t('event.end', locale) }}</label>
                                    <date-picker mode="dateTime" :is24hr="true" v-model="newEvent.endDate" :locale="'de'">
                                        <template v-slot="{ inputValue, inputEvents }">
                                            <input type="text" 
                                                   class="form-control"
                                                   :value="inputValue"
                                                   v-on="inputEvents">
                                        </template>
                                    </date-picker>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="eventDescription">{{ $t('event.description', locale) }}</label>
                            <small class="help-text">{{ $t('event.description.help', locale) }}</small>
                            <textarea id="eventDescription" class="form-control" rows="5" v-model="newEvent.description" required></textarea>
                        </div>

                        <div class="form-group">
                            <label>{{ $t('event.links', locale) }}</label>
                            <small class="help-text">{{ $t('event.links.help', locale) }}</small>
                            <div class="links-list">
                                <div v-for="(link, index) in newEvent.links" :key="index" class="link-item">
                                    <input type="text" class="form-control" v-model="link.label" :placeholder="$t('event.links.label', locale)">
                                    <input type="text" class="form-control" v-model="link.value" placeholder="URL">
                                    <button type="button" class="button error" @click="removeLink(index)">
                                        <span class="material-icons">{{ $t('job.links.remove', locale) }}</span>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="button primary" @click="addLink">{{ $t('event.links.add', locale) }}</button>
                        </div>
                    </div>
                     <!-- Dokumente  -->
                    <div class="form-group documents-section">
                        <label>{{ $t('event.files', locale) }}</label>
                        <small class="help-text">{{ $t('event.files.help', locale) }}</small>
                        <file-selector 
                            :items="newEvent.files"
                            :addLabel="$t('event.files.upload', locale)"
                            :cancel-label="$t('event.cancel', locale)"
                            :allowedTypes="'.pdf,.jpg,.jpeg,.png'"
                            @changed="updateFiles">
                        </file-selector>
                    </div>

                    <hr class="form-section-divider">

                    <div class="event-form-footer">
                        <button type="button" class="button warning" @click="showEventModal = false">{{ $t('event.cancel', locale) }}</button>
                        <button type="submit" class="button primary">{{ $t('event.save', locale) }}</button>
                    </div>
                </form>
            </div>
        </EventModal>
    </div>
</template>

<script>
import {mapGetters, mapState} from 'vuex';
import { translateField } from '../utils/filters';
import EmbedEventsView from './EmbedEventsView';
import {track, trackDevice, trackPageView} from '../utils/logger';
import EventModal from './EventModal.vue';
import FileSelector from './FileSelector.vue';
import TagSelector from './TagSelector.vue';
import { DatePicker } from 'v-calendar';
import 'v-calendar/dist/style.css';

export default {

    components: {
        EmbedEventsView,
        EventModal,
        FileSelector,
        TagSelector,
        DatePicker,
    },

    data() {
        return {
            isLoading: false,
            isLoadingMore: false,
            isLoadedFully: false,
            events: [],
            term: '',
            type: null,
            archive: 0,
            filters: [],
            limit: 30,
            offset: 0,
            activeFilterSelect: null,
            event: null,

            // New feature data
            showEventModal: false,
            newEvent: {
                title: '',
                type: 'external',
                color: null,
                location: '',
                organizer: '',
                description: '',
                registration: '',
                startDate: '',
                endDate: '',
                contactInfo: {
                    name: '',
                    email: '',
                    phone: '',
                    department: ''
                },
                topics: [],
                languages: [],
                locations: [],
                links: [],
                files: []
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
                    .filter(e => !e.context || e.context === 'event')
                    .map(this.$clientOptions?.middleware?.mapTopics || (e => e))
                    .filter(this.$clientOptions?.middleware?.filterTopics || (e => e.isPublic))
                    .sort(this.$clientOptions?.middleware?.sortTopics || ((a, b) => a.position - b.position));
            },
            languages: function (state) {
                return state.languages.all
                    .filter(e => !e.context || e.context === 'event')
                    .map(this.$clientOptions?.middleware?.mapLanguages || (e => e))
                    .filter(this.$clientOptions?.middleware?.filterLanguages || (e => e.isPublic))
                    .sort(this.$clientOptions?.middleware?.sortLanguages || ((a, b) => a.position - b.position));
            },
            locations: function (state) {
                return state.locations.all
                    .filter(e => !e.context || e.context === 'event')
                    .map(this.$clientOptions?.middleware?.mapLocations || (e => e))
                    .filter(this.$clientOptions?.middleware?.filterLocations|| (e => e.isPublic))
                    .sort(this.$clientOptions?.middleware?.sortLocations|| ((a, b) => a.position - b.position));
            },
        }),
        ...mapGetters({
            getTopicById: 'topics/getById',
            getLanguageById: 'languages/getById',
            getLocationById: 'locations/getById',
        }),
    },

    methods: {

        translateField,

        keyUp (event) {
            if(event.keyCode === 27) {
                this.activeFilterSelect = null;
                this.event = null;
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

            if(this.type === 'regiosuisse') {
                params.type = ['regiosuisse', 'fsk', 'cafe-r', 'einstiegskurs', 'konferenz', 'wissenschaftsforum'];
            }

            params.archive = this.archive;
            params.limit = this.limit;
            params.offset = this.offset;

            return params;
        },

        clickFilterSelect(name) {
            if(this.activeFilterSelect === name) {
                if(!this.disableTelemetry) {
                    track('Event Filter', 'Hide', name);
                }
                return this.activeFilterSelect = null;
            }

            if(!this.disableTelemetry) {
                track('Event Filter', 'Show', name);
            }

            this.activeFilterSelect = name;
        },

        clickToggleType() {
            this.type = this.type === 'regiosuisse' ? null : 'regiosuisse';

            if(this.history) {
                window.history.replaceState(null, null, this.getHistoryQueryString());
            }

            this.reload();
        },

        clickToggleArchive() {
            this.archive = this.archive === 1 ? 0 : 1;

            if(this.history) {
                window.history.replaceState(null, null, this.getHistoryQueryString());
            }

            this.reload();
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
                    track('Event Filter', 'Disable', {
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
                track('Event Filter', 'Enable', {
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
                track('Event Search', 'Change', this.term);
            }
        },

        clickLoadMore() {
            this.isLoadingMore = true;
            this.offset += this.limit;
            let currentCount = this.events.length;

            return this.$store.dispatch('events/loadFiltered', this.getFilterParams()).then((events) => {
                this.events = [
                    ...this.events,
                    ...events,
                ];

                if(currentCount >= this.events.length || events.length < this.limit) {
                    this.isLoadedFully = true;
                }

                this.isLoadingMore = false;

                if(!this.disableTelemetry) {
                    track('Event Navigation', 'Load More', {
                        isLoadedFully: this.isLoadedFully,
                        limit: this.limit,
                        offset: this.offset,
                        count: events.length,
                    });
                }

            });
        },

        clickShowEvent(event) {
            if(this.history) {
                window.history.pushState(null, null, this.getHistoryQueryString(event));
            }

            if(!this.disableTelemetry) {
                track('Event Navigation', 'Show Event', {
                    id: event.id,
                    title: translateField(event, 'title', this.locale),
                });
            }

            this.event = event;
        },

        clickHideEvent() {
            if(this.history) {
                window.history.pushState(null, null, this.getHistoryQueryString());
            }

            if(!this.disableTelemetry) {
                track('Event Navigation', 'Hide Event', {
                    id: this.event.id,
                    title: translateField(this.event, 'title', this.locale),
                });
            }

            this.event = null;
        },

        popState(event) {
            this.event = null;

            if(this.getUrlParams()['event-id']) {
                this.$store.dispatch('events/load', this.getUrlParams()['event-id']).then((event) => {
                    this.event = event;
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

                if(!['topics', 'languages', 'locations'].includes(k)) {
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

        getHistoryQueryString(event) {

            let result = [];

            if(event) {
                result.push('event-id='+event.id+'&title='+encodeURIComponent(translateField(event, 'title', this.locale)));
            }

            if(this.term) {
                result.push('term='+encodeURIComponent(this.term));
            }

            if(this.type) {
                result.push('type='+encodeURIComponent(this.type));
            }

            if(this.archive) {
                result.push('archive='+encodeURIComponent(this.archive));
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
            this.type = this.getUrlParams()['type'];
            this.archive = this.getUrlParams()['archive'] ? this.getUrlParams()['archive'] : 0;

            let filters = [];

            ['topic', 'language', 'location'].forEach((key) => {

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
            this.offset = 0;
            this.isLoadedFully = false;
            this.isLoadingMore = false;

            return this.$store.dispatch('events/loadFiltered', this.getFilterParams()).then((events) => {
                this.events = [
                    ...events,
                ];

                if(events.length < this.limit) {
                    this.isLoadedFully = true;
                }

                this.isLoading = false;
            });
        },

        // New feature methods
        submitEvent() {
            // Validate email
            if (!this.newEvent.contactInfo.email) {
                this.modal = {
                    type: 'error',
                    message: this.$t('event.error.email_required', this.locale)
                };
                return;
            }

            // Transform topics, languages, and locations data
            const topics = Array.isArray(this.newEvent.topics) ? 
                this.newEvent.topics.map(topic => typeof topic === 'object' ? topic.id : topic) : [];
            const languages = Array.isArray(this.newEvent.languages) ? 
                this.newEvent.languages.map(language => typeof language === 'object' ? language.id : language) : [];
            const locations = Array.isArray(this.newEvent.locations) ? 
                this.newEvent.locations.map(location => typeof location === 'object' ? location.id : location) : [];

            const eventData = {
                title: this.newEvent.title,
                type: this.newEvent.type,
                color: this.newEvent.type === 'regiosuisse' || this.newEvent.type === 'cafe-r' ? (this.newEvent.color || 1) : null,
                location: this.newEvent.location,
                organizer: this.newEvent.organizer,
                description: this.newEvent.description,
                registration: this.newEvent.registration,
                startDate: this.newEvent.startDate,
                endDate: this.newEvent.endDate,
                contact: [
                    this.newEvent.contactInfo.name && `Kontaktperson: ${this.newEvent.contactInfo.name}`,
                    this.newEvent.contactInfo.email && `E-Mail: ${this.newEvent.contactInfo.email}`,
                    this.newEvent.contactInfo.phone && `Telefon: ${this.newEvent.contactInfo.phone}`,
                    this.newEvent.contactInfo.department && `Abteilung: ${this.newEvent.contactInfo.department}`
                ].filter(Boolean).join('\n'),
                topics: topics,
                languages: languages,
                locations: locations,
                links: this.newEvent.links || [],
                files: this.newEvent.files.map(file => ({
                    ...file,
                    id: file.id,
                    name: file.name,
                    extension: file.extension,
                    mimeType: file.mimeType,
                    hash: file.hash
                })) || [],
                translations: {
                    [this.locale]: {
                        title: this.locale !== 'de' ? this.newEvent.title : null,
                        description: this.locale !== 'de' ? this.newEvent.description : null,
                    }
                },
                contactInfo: {
                    name: this.newEvent.contactInfo.name,
                    email: this.newEvent.contactInfo.email,
                    phone: this.newEvent.contactInfo.phone,
                    department: this.newEvent.contactInfo.department
                }
            };

            this.$store.dispatch('events/createFromEmbed', eventData)
                .then(response => {
                    // Show success message
                    this.modal = {
                        type: 'success',
                        message: this.$t('event.success.created', this.locale)
                    };
                    
                    // Open the new event in a new tab if there's a redirect URL
                    if (response.redirectUrl) {
                        window.location.href = response.redirectUrl;
                    }
                    
                    // Reset form
                    this.showEventModal = false;
                    this.newEvent = {
                        title: '',
                        type: 'external',
                        color: null,
                        location: '',
                        organizer: '',
                        description: '',
                        registration: '',
                        startDate: '',
                        endDate: '',
                        contactInfo: {
                            name: '',
                            email: '',
                            phone: '',
                            department: ''
                        },
                        topics: [],
                        languages: [],
                        locations: [],
                        links: [],
                        files: []
                    };
                    
                    // Reload events list
                    this.reload();
                })
                .catch(error => {
                    this.modal = {
                        type: 'error',
                        message: error.response?.data?.error || this.$t('event.error.general', this.locale)
                    };
                });
        },

        updateFiles(files) {
            this.newEvent.files = files;
        },

        addLink() {
            this.newEvent.links.push({
                label: '',
                value: ''
            });
        },

        removeLink(index) {
            this.newEvent.links.splice(index, 1);
        },
    },

    created() {
        this.limit = this.$clientOptions?.limit || this.limit;
        this.filters = this.$clientOptions?.defaultFilters || [];

        if(this.history && this.getUrlParams()['term']) {
            this.term = this.getUrlParams()['term'];
        }

        if(this.history && this.getUrlParams()['type']) {
            this.type = this.getUrlParams()['type'];
        }

        if(this.history && this.getUrlParams()['archive']) {
            this.archive = this.getUrlParams()['archive'] ? this.getUrlParams()['archive'] : 0;
        }

        this.reload();

        Promise.all([
            this.$store.dispatch('topics/loadAll'),
            this.$store.dispatch('languages/loadAll'),
            this.$store.dispatch('locations/loadAll'),
        ]).then(() => {
            this.filters = this.filters
                .filter((filter) => {
                    return ['topic', 'language', 'location'].includes(filter.type);
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

        if(this.history && this.getUrlParams()['event-id']) {
            this.$store.dispatch('events/load', this.getUrlParams()['event-id']).then((event) => {
                this.event = event;
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
    },

    watch: {
        'newEvent.type': {
            handler(newType) {
                if (newType === 'regiosuisse' && !this.newEvent.color) {
                    // Set default color to green (id: 1) for regiosuisse events
                    this.newEvent.color = 1;
                } else if (newType === 'external') {
                    this.newEvent.color = null;
                }
            },
            immediate: true
        }
    },

};
</script>

<template>

    <div class="circular-economy-projects-component">

        <div class="circular-economy-projects-component-title">

            <h2>KLW Projekte</h2>

            <transition name="fade" mode="out-in">
                <div class="loading-indicator" v-if="isLoading('circularEconomyProjects')"></div>
            </transition>

            <div class="circular-economy-projects-component-title-actions">
                <router-link :to="'/circular-economy-projects/add'" class="button primary">Neuen Eintrag erstellen</router-link>
            </div>

        </div>

        <div class="circular-economy-projects-component-filter">

            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="term">Suchbegriff</label>
                        <input id="term" type="text" class="form-control" v-model="term" @change="changeForm()">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-3">
                    <div class="form-group">
                        <label for="topics">Thema</label>
                        <div class="select-wrapper">
                            <select id="topics" class="form-control" @change="addFilter({type: 'topic', value: $event.target.value}); $event.target.value = null;">
                                <option></option>
                                <option v-for="topic in topics.filter(topic => !topic.context || topic.context === 'circularEconomyProject')">{{topic.name}}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        <label for="businessSectors">Geschäftsfeld</label>
                        <div class="select-wrapper">
                            <select id="businessSectors" class="form-control" @change="addFilter({type: 'businessSector', value: $event.target.value}); $event.target.value = null;">
                                <option></option>
                                <option v-for="businessSector in businessSectors.filter(businessSector => !businessSector.context || businessSector.context === 'circularEconomyProject')">{{businessSector.name}}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        <label for="instruments">Geschäftsfeld</label>
                        <div class="select-wrapper">
                            <select id="instruments" class="form-control" @change="addFilter({type: 'instrument', value: $event.target.value}); $event.target.value = null;">
                                <option></option>
                                <option v-for="instrument in instruments.filter(instrument => !instrument.context || instrument.context === 'circularEconomyProject')">{{instrument.name}}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        <label for="geographicRegions">Geographische Region</label>
                        <div class="select-wrapper">
                            <select id="geographicRegions" class="form-control" @change="addFilter({type: 'geographicRegion', value: $event.target.value}); $event.target.value = null;">
                                <option></option>
                                <option v-for="geographicRegion in geographicRegions.filter(geographicRegion => !geographicRegion.context || geographicRegion.context === 'circularEconomyProject')">{{geographicRegion.name}}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="projects-component-filter-tags">
                <div class="tag" v-for="filter of filters" @click="removeFilter({type: filter.type, value: filter.value})">
                    <strong v-if="filter.type === 'topic'">Thema:</strong>
                    <strong v-if="filter.type === 'businessSector'">Geschäftsfeld:</strong>
                    <strong v-if="filter.type === 'instrument'">Finanzierung:</strong>
                    <strong v-if="filter.type === 'geographicRegion'">Gegographische Region:</strong>
                    {{filter.value}}
                </div>
            </div>

        </div>

        <div class="circular-economy-projects-component-content">

            <table class="table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Bezeichnung</th>
                    <th>Typ</th>
                    <th>Thema</th>
                    <th>Geschäftsfeld</th>
                    <th>Finanzierung</th>
                    <th>Geographische Region</th>
                </tr>
                </thead>
                <tbody v-if="!circularEconomyProjects.length && isLoading('circularEconomyProjects')">
                    <tr>
                        <td colspan="7"><em>Einträge werden geladen...</em></td>
                    </tr>
                </tbody>
                <tbody v-else>
                    <tr v-for="circularEconomyProject in circularEconomyProjects"
                        class="clickable"
                        :class="{'warning': !circularEconomyProject.isPublic}"
                        @click="clickCircularEconomyProject(circularEconomyProject)">
                        <td>{{ circularEconomyProject.id }}</td>
                        <td>{{ translateField(circularEconomyProject, 'title', 'de') }}</td>
                        <td v-if="circularEconomyProject.type === 'project'">Projekt</td>
                        <td v-else>Beispielhaft</td>
                        <td>{{ formatOneToMany(circularEconomyProject.topics, getTopicById) }}</td>
                        <td>{{ formatOneToMany(circularEconomyProject.businessSectors, getBusinessSectorById) }}</td>
                        <td>{{ formatOneToMany(circularEconomyProject.instruments, getInstrumentById) }}</td>
                        <td>{{ formatOneToMany(circularEconomyProject.geographicRegions, getGeographicRegionById) }}</td>
                    </tr>
                </tbody>
            </table>

        </div>

    </div>

</template>

<script>
import { mapState, mapGetters } from 'vuex';
import moment from 'moment';
import { translateField } from '../utils/filters';

export default {
    data () {
        return {
            term: '',
            filters: [],
        };
    },
    computed: {
        ...mapState({
            circularEconomyProjects: state => state.circularEconomyProjects.filtered,
            topics: state => state.topics.all,
            businessSectors: state => state.businessSectors.all,
            instruments: state => state.instruments.all,
            geographicRegions: state => state.geographicRegions.all,
        }),
        ...mapGetters({
            isLoading: 'loaders/isLoading',
            getCircularEconomyProjectById: 'circularEconomyProjects/getById',
            getTopicById: 'topics/getById',
            getBusinessSectorById: 'businessSectors/getById',
            getInstrumentById: 'instruments/getById',
            getGeographicRegionById: 'geographicRegions/getById',
        }),
    },
    methods: {
        changeForm () {
            this.saveFilter();
            this.reloadCircularEconomyProjects();
        },
        getFilterParams () {
            let params = {};
            params.term = this.term;

            this.filters.forEach((filter) => {
                if(!params[filter.type]) {
                    params[filter.type] = [];
                }
                params[filter.type].push(filter.value);
            });

            return params;
        },
        reloadCircularEconomyProjects () {
            return this.$store.dispatch('circularEconomyProjects/loadFiltered', this.getFilterParams());
        },
        clickCircularEconomyProject (circularEconomyProject) {
            this.$router.push({
                path: '/circular-economy-projects/'+circularEconomyProject.id+'/edit'
            });
        },
        formatOneToMany (items, getter) {
            let result = [];
            items.forEach((item) => {
                result.push(getter(item.id)?.name);
            });

            return result.join(', ');
        },
        formatDate(date, format = 'DD.MM.YYYY') {
            if(date && moment(date)) {
                return moment(date).format(format);
            }
        },
        formatDateTime(date) {
            return this.formatDate(date, 'DD.MM.YYYY HH:mm');
        },
        addFilter (filter) {
            if(!filter.value) {
                return;
            }
            if(this.filters.filter(f => f.type === filter.type).find(f => f.value === filter.value)) {
                return;
            }
            this.filters.push(filter);
            this.changeForm();
        },
        removeFilter (filter) {
            let f = this.filters.filter(f => f.type === filter.type).find(f => f.value === filter.value);
            if(f) {
                this.filters.splice(this.filters.indexOf(f), 1);
            }
            this.changeForm();
        },
        saveFilter () {
            window.sessionStorage.setItem('regiosuisse.circularEconomyProjects.filters', JSON.stringify(this.filters));
            window.sessionStorage.setItem('regiosuisse.circularEconomyProjects.term', this.term);
        },
        loadFilter () {
            this.filters = JSON.parse(window.sessionStorage.getItem('regiosuisse.circularEconomyProjects.filters') || '[]');
            this.term = window.sessionStorage.getItem('regiosuisse.circularEconomyProjects.term') || '';
        },
        translateField,
    },
    created () {
        this.loadFilter();
        this.reloadCircularEconomyProjects();
    },
}
</script>
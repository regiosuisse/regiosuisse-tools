<template>

    <div class="publications-component">

        <div class="publications-component-title">
            <h2>Publikationen</h2>

            <transition name="fade" mode="out-in">
                <div class="loading-indicator" v-if="isLoading('publications')"></div>
            </transition>

            <div class="publications-component-title-actions">
                <router-link :to="'/publications/add'" class="button primary">Neuen Eintrag erstellen</router-link>
            </div>
        </div>

        <div class="publications-component-content">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Typ</th>
                        <th>Titel</th>
                        <th>Autor</th>
                        <th>Organisation</th>
                        <th>Lizenz</th>
                        <th>Thema</th>
                        <th>Sprache</th>
                        <th>Geographische Region</th>
                        <th>Datum</th>
                    </tr>
                </thead>
                <tbody v-if="!publications.length && isLoading('publications')">
                    <tr>
                        <td colspan="4"><em>Einträge werden geladen...</em></td>
                    </tr>
                </tbody>
                <tbody>
                    <tr v-for="element in publications" class="clickable" @click="clickPublication(element)" :class="{ 'warning': !element.isPublic }">
                        <td>{{ element.id }}</td>
                        <td v-if="element.type === 'project'">Projekt</td>
                        <td v-else>Publikation</td>
                        <td>{{ translateField(element, 'title', 'de') }}</td>
                        <td>{{ element.authors.map(e => e.name).join(', ') }}</td>
                        <td>{{ element.organizations.map(e => e.name).join(', ') }}</td>
                        <td v-if="element.licenseType">{{ translateField(licenseTypes.find(lt => lt.type === element.licenseType), 'name', 'de') }}</td>
                        <td v-else>-</td>
                        <td>{{ formatOneToMany(element.topics, getTopicById) }}</td>
                        <td>{{ formatOneToMany(element.languages, getLanguageById) }}</td>
                        <td>{{ formatOneToMany(element.geographicRegions, getGeographicRegionById) }}</td>
                        <td v-if="element.startDate === element.endDate">{{ $helpers.formatDate(element.startDate, 'YYYY') || '-' }}</td>
                        <td v-else-if="element.endDate">{{ $helpers.formatDateRange(element.startDate, element.endDate, 'YYYY') }}</td>
                        <td v-else>-</td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>

</template>

<script>
import { mapState, mapGetters } from 'vuex';
import { translateField } from '../utils/filters';
import {licenseTypes} from '../common/licenseTypes';

export default {
    data () {
        return {
            licenseTypes,
            term: '',
            filters: [],
        };
    },
    components: {},
    computed: {
        ...mapState({
            publications: state => state.publications.filtered,
            topics: state => state.topics.all,
            languages: state => state.languages.all,
            geographicRegions: state => state.geographicRegions.all,
        }),
        ...mapGetters({
            isLoading: 'loaders/isLoading',
            getGeographicRegionById: 'geographicRegions/getById',
            getLanguageById: 'languages/getById',
            getTopicById: 'topics/getById',
        }),
    },
    methods: {
        clickPublication (publication) {
            this.$router.push({ path: '/publications/' + publication.id + '/edit' });
        },
        formatOneToMany (items, getter) {
            return items.map(item => getter(item.id)?.name).join(', ');
        },
        reloadPublications () {
            return this.$store.dispatch('publications/loadFiltered', this.getFilterParams());
        },
        getFilterParams () {
            let params = { term: this.term };
            this.filters.forEach(filter => {
                if (!params[filter.type]) params[filter.type] = [];
                params[filter.type].push(filter.value);
            });
            return params;
        },
        addFilter (filter) {
            if (!filter.value || this.filters.some(f => f.type === filter.type && f.value === filter.value)) return;
            this.filters.push(filter);
            this.changeForm();
        },
        removeFilter (filter) {
            const index = this.filters.findIndex(f => f.type === filter.type && f.value === filter.value);
            if (index !== -1) {
                this.filters.splice(index, 1);
                this.changeForm();
            }
        },
        changeForm () {
            this.saveFilter();
            this.reloadPublications();
        },
        saveFilter () {
            sessionStorage.setItem('regiosuisse.publications.filters', JSON.stringify(this.filters));
            sessionStorage.setItem('regiosuisse.publications.term', this.term);
        },
        loadFilter () {
            this.filters = JSON.parse(sessionStorage.getItem('regiosuisse.publications.filters') || '[]');
            this.term = sessionStorage.getItem('regiosuisse.publications.term') || '';
        },
        translateField,
    },
    created () {
        this.loadFilter();
        this.reloadPublications();
    },
}
</script>
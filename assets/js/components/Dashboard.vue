<template>

    <div class="dashboard-component">

        <div class="dashboard-component-form">

            <div class="dashboard-component-form-header">

                <h3>Dashboard</h3>

                <div class="interactive-graphics-component-form-header-actions">
                    <div class="form-group">
                        <DatePicker mode="date" v-model="dateFrom" :locale="'de'">
                            <template v-slot="{ inputValue, inputEvents }">
                                <input type="text" class="form-control"
                                       :value="inputValue"
                                       v-on="inputEvents"
                                       id="startDate">
                            </template>
                        </DatePicker>
                    </div>
                    &nbsp;
                    <div class="form-group">
                        <DatePicker mode="date" v-model="dateTo" :locale="'de'">
                            <template v-slot="{ inputValue, inputEvents }">
                                <input type="text" class="form-control"
                                       :value="inputValue"
                                       v-on="inputEvents"
                                       id="startDate">
                            </template>
                        </DatePicker>
                    </div>
                </div>

            </div>

        </div>

        <div class="dashboard-component-list">

            <div class="dashboard-component-list-item" style="min-width: 75%;">

                <h4>
                    Feedback:
                    <span :class="{success: feedbackAverage > 50, error: feedbackAverage <= 50}">{{ Math.round(feedbackAverage * 100) / 100 }}% Positiv</span>
                </h4>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Datum</th>
                            <th>Ursprung</th>
                            <th>Bewertung</th>
                            <th>Kommentar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="feedback in feedbacks">
                            <td>{{ formatDate(feedback.createdAt) }}</td>
                            <td><a :href="feedback.action" target="_blank" style="text-decoration: underline;">{{ feedback.action }}</a></td>
                            <td class="success" v-if="JSON.parse(feedback.value)?.isGood">Positiv</td>
                            <td class="error" v-else>Negativ</td>
                            <td>{{ JSON.parse(feedback.value)?.comment || '-' }}</td>
                        </tr>
                        <tr v-if="!feedbacks.length">
                            <td colspan="4"><em>Keine Feedbacks gefunden</em></td>
                        </tr>
                    </tbody>
                </table>

            </div>

            <div class="dashboard-component-list-item">

                <h4>
                    Beliebteste Projekte
                </h4>

                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titel</th>
                            <th>Aufrufe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="project in popularProjects.slice(0, 20)">
                            <td>{{ project.id }}</td>
                            <td>{{ project.title }}</td>
                            <td>{{ project.count }}</td>
                        </tr>
                        <tr v-if="!popularProjects.length">
                            <td colspan="3"><em>Keine beliebten Projekte gefunden</em></td>
                        </tr>
                    </tbody>
                </table>

            </div>

            <div class="dashboard-component-list-item">

                <h4>
                    Beliebteste Agenda-Einträge
                </h4>

                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titel</th>
                            <th>Aufrufe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="event in popularEvents.slice(0, 20)">
                            <td>{{ event.id }}</td>
                            <td>{{ event.title }}</td>
                            <td>{{ event.count }}</td>
                        </tr>
                        <tr v-if="!popularEvents.length">
                            <td colspan="3"><em>Keine beliebten Agenda-Einträge gefunden</em></td>
                        </tr>
                    </tbody>
                </table>

            </div>

            <div class="dashboard-component-list-item" style="min-width: 75%;">

                <h4>
                    Beliebteste Tools
                </h4>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Ursprung</th>
                            <th>Aufrufe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="referer in popularTools.slice(0, 20)">
                            <td><span style="word-break: break-all">{{ referer.referer }}</span></td>
                            <td>{{ referer.count }}</td>
                        </tr>
                        <tr v-if="!popularTools.length">
                            <td colspan="2"><em>Keine beliebten Tools gefunden</em></td>
                        </tr>
                    </tbody>
                </table>

            </div>

        </div>

    </div>

</template>

<script>
    import { mapState, mapGetters } from 'vuex';
    import {DatePicker} from 'v-calendar';
    import moment from 'moment';

    export default {
        data () {
            return {
                dateFrom: moment().subtract(1, 'months').toDate(),
                dateTo: moment().toDate(),
            };
        },
        components: {
            DatePicker,
        },
        computed: {
            ...mapState({
                logs: state => state.logs.filtered,
            }),
            ...mapGetters({
            }),
            feedbacks () {
                return this.logs.filter(log => log.category === 'Feedback');
            },
            feedbackAverage() {
                let total = this.feedbacks.length;

                if(!total) {
                    return 100;
                }

                let positive = this.feedbacks.reduce((acc, f) => {
                    return acc + (JSON.parse(f.value)?.isGood ? 1 : 0);
                }, 0);

                return 100 / total * positive;
            },
            popularTools() {

                const rows = this.logs.filter(
                    log =>
                        log.category === 'Device Stats' && log.referer
                );

                const referers = {};

                rows.forEach(row => {

                    referers[row.referer] = {
                        referer: row.referer,
                        count: (referers[row.referer]?.count || 0) + 1,
                    };

                });

                return Object.values(referers)
                    .sort((a, b) => b.count - a.count);

            },
            popularProjects() {

                const rows = this.logs.filter(
                    log =>
                        log.category === 'Project Navigation' &&
                        log.action === 'Show Project'
                );

                const projects = {};

                rows.forEach(row => {

                    const value = JSON.parse(row.value);

                    projects[value.id] = {
                        id: value.id,
                        title: value.title,
                        count: (projects[value.id]?.count || 0) + 1,
                    };

                });

                return Object.values(projects)
                    .sort((a, b) => b.count - a.count);

            },
            popularEvents () {

                const rows = this.logs.filter(
                    log =>
                        log.category === 'Event Navigation' &&
                        log.action === 'Show Event'
                );

                const events = {};

                rows.forEach(row => {

                    const value = JSON.parse(row.value);

                    events[value.id] = {
                        id: value.id,
                        title: value.title,
                        count: (events[value.id]?.count || 0) + 1,
                    };

                });

                return Object.values(events)
                    .sort((a, b) => b.count - a.count);
            },
        },
        methods: {
            reload () {
                this.$store.dispatch('logs/loadFiltered', {
                    createdAtFrom: moment(this.dateFrom).format('YYYY-MM-DD')+' 00:00:00',
                    createdAtTo: moment(this.dateTo).format('YYYY-MM-DD')+' 23:59:59',
                });
            },
            formatDate(date, format = "DD.MM.YYYY") {
                if (date && moment(date)) {
                    return moment(date).format(format);
                }
            },
        },
        created () {
            this.reload();
        },
        watch: {
            dateFrom () {
                this.reload();
            },
            dateTo () {
                this.reload();
            },
        },
    }
</script>
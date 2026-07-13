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

            <div class="dashboard-component-list-item">

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
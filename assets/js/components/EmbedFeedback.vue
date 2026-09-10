<template>

    <div class="embed-feedback" :class="[$env.INSTANCE_ID+'-feedback', {'is-responsive': responsive}]">

        <template v-if="isSent">
            <div class="embed-feedback-question">{{ $t('Vielen Dank für Ihr Feedback!', locale) }}</div>
        </template>

        <template v-else>

            <div class="embed-feedback-question">{{ $t('War diese Seite hilfreich?', locale) }}</div>

            <div class="embed-feedback-answer">
                <button class="embed-feedback-button"
                        :class="{'is-active': log.value.isGood === true}"
                        @click="log.value.isGood = true; log.value.comment = '';">{{ $t('Ja', locale) }}</button>
                <button class="embed-feedback-button"
                        :class="{'is-active': log.value.isGood === false}"
                        @click="log.value.isGood = false">{{ $t('Nein', locale) }}</button>
            </div>

            <div class="embed-feedback-comment" v-if="log.value.isGood === false">
                <label for="comment">{{ $t('Kommentar', locale) }}</label>
                <textarea name="comment" cols="30" rows="6" v-model="log.value.comment"></textarea>
            </div>

            <div class="embed-feedback-actions" v-if="!isSent && (log.value.isGood === true || log.value.isGood === false)">
                <button class="embed-feedback-button"
                        @click="submit()"
                        v-if="!isLoading">{{ $t('Absenden', locale) }}</button>
                <button class="embed-feedback-button is-disabled"
                        v-else>{{ $t('Wird verarbeitet...', locale) }}</button>
            </div>

        </template>



    </div>

</template>

<script>

import {mapGetters, mapState} from 'vuex';
import { translateField } from '../utils/filters';
import api from '../api';

export default {

    components: {
    },

    data() {
        return {
            log: {
                context: 'Telemetry',
                category: 'Feedback',
                action: window.location.href,
                value: {
                    isGood: null,
                    comment: '',
                },
            },
            isLoading: false,
            isSent: false,
        };
    },

    computed: {
        locale () {
            return this.$clientOptions?.locale || 'de';
        },
        responsive () {
            return this.$clientOptions?.responsive ?? true;
        },
    },

    methods: {

        translateField,

        async submit() {
            this.isLoading = true;

            await api.logs.pixel({
                ...this.log,
            });

            this.isLoading = false;
            this.isSent = true;
        },

    },

    created() {
    },

    mounted() {
    },

    beforeUnmount() {
    }

};

</script>

<style lang="scss">



</style>

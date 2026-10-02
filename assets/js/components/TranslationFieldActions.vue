<template>

    <div class="translation-field-actions" @click.stop>

        <button type="button"
                class="translation-field-actions-trigger"
                @click="isOpen = !isOpen"
                title="Sprachaktionen">

            <span class="material-icons">sync_alt</span>

        </button>

        <div class="translation-field-actions-menu" v-if="isOpen">

            <div class="translation-field-actions-menu-row" v-if="canCopy">

                <span class="translation-field-actions-menu-label">
                    <span class="material-icons">content_copy</span>
                    Kopieren von
                </span>

                <div class="translation-field-actions-menu-buttons">

                    <button type="button"
                            class="button"
                            v-for="sourceLocale in sourceLocales"
                            :key="'copy-'+sourceLocale"
                            @click="clickAction('copy', sourceLocale)">

                        {{ sourceLocale.toUpperCase() }}

                    </button>

                </div>

            </div>

            <div class="translation-field-actions-menu-row" v-if="canTranslate">

                <span class="translation-field-actions-menu-label">
                    <span class="material-icons">translate</span>
                    Übersetzen von
                </span>

                <div class="translation-field-actions-menu-buttons">

                    <button type="button"
                            class="button"
                            v-for="sourceLocale in sourceLocales"
                            :key="'translate-'+sourceLocale"
                            @click="clickAction('translate', sourceLocale)">

                        {{ sourceLocale.toUpperCase() }}

                    </button>

                </div>

            </div>

        </div>

    </div>

</template>

<script>

export default {
    props: {
        locale: {
            type: String,
            required: true,
        },
        payload: {
            type: Object,
            required: true,
        },
        canCopy: {
            type: Boolean,
            default: true,
        },
        canTranslate: {
            type: Boolean,
            default: true,
        },
    },
    emits: [
        'copy',
        'translate',
    ],
    data() {
        return {
            isOpen: false,
        };
    },
    computed: {
        sourceLocales() {
            return ['de', 'fr', 'it'].filter(locale => locale !== this.locale);
        },
    },
    mounted() {
        document.addEventListener('click', this.close);
    },
    beforeUnmount() {
        document.removeEventListener('click', this.close);
    },
    methods: {
        close() {
            this.isOpen = false;
        },
        clickAction(action, sourceLocale) {

            this.$emit(action, {
                sourceLocale: sourceLocale,
                targetLocale: this.locale,
                payload: this.payload,
            });

            this.isOpen = false;

        },
    },
};

</script>
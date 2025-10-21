<template>

    <div class="embed-financial-supports-view" :class="$env.INSTANCE_ID+'-financial-supports-view'">

        <div class="embed-financial-supports-view-header">

            <h1 class="embed-financial-supports-view-header-title">{{ translateField(financialSupport, 'name', locale) }}</h1>

            <a class="embed-financial-supports-view-header-close" @click="clickClose()"></a>

        </div>

        <div class="embed-financial-supports-view-content">

            <div class="embed-financial-supports-view-content-text"
                 v-html="translateField(financialSupport, 'description', locale)"></div>

            <div class="embed-financial-supports-view-content-text">
                <h2>{{ $t('Kurzbeschrieb', locale) }}</h2>
                <div v-html="translateField(financialSupport, 'additionalInformation', locale)"></div>
            </div>

            <div class="embed-financial-supports-view-content-text">
                <h2>{{ $t('Teilnahmekriterien', locale) }}</h2>
                <div v-html="translateField(financialSupport, 'inclusionCriteria', locale)"></div>
            </div>

            <div class="embed-financial-supports-view-content-text">
                <h2>{{ $t('Ausschlusskriterien', locale) }}</h2>
                <div v-html="translateField(financialSupport, 'exclusionCriteria', locale)"></div>
            </div>

            <div class="embed-financial-supports-view-content-text">
                <h2>{{ $t('Finanzierung', locale) }}</h2>
                <div v-html="translateField(financialSupport, 'financingRatio', locale)"></div>
            </div>

            <div class="embed-financial-supports-view-content-text">
                <h2>{{ $t('Gesuchstellung', locale) }}</h2>
                <div v-html="translateField(financialSupport, 'application', locale)"></div>
            </div>

            <div class="embed-financial-supports-view-content-contacts" v-if="translateField(financialSupport, 'contacts', locale)">
                <h2>{{ $t('Kontakt', locale) }}</h2>
                <div class="embed-financial-supports-view-content-contacts-contact" v-for="contact in translateField(financialSupport, 'contacts', locale)">
                    <p>
                        <template v-if="contact.name">
                            <strong>{{ contact.name }}</strong><br>
                        </template>
                        <template v-if="contact.firstName && contact.lastName">
                            {{ contact.title || '' }} {{ contact.firstName }} {{ contact.lastName }}<br>
                        </template>
                        <template v-if="contact.role">
                            {{ contact.role }}<br>
                        </template>
                        <template v-if="contact.street">
                            {{ contact.street }}<br>
                        </template>
                        <template v-if="contact.zipCode && contact.city">
                            {{ contact.zipCode || '' }} {{ contact.city || '' }}<br>
                        </template>
                        <template v-if="contact.phone">
                            <a :href="'tel:'+contact.phone">{{ contact.phone }}</a><br>
                        </template>
                        <template v-if="contact.email">
                            <a :href="'mailto:'+contact.email">{{ contact.email }}</a><br>
                        </template>
                        <template v-if="contact.website && contact.website.startsWith('http')">
                            <a :href="contact.website" target="_blank">{{ contact.website.split('://', 2)[1] }}</a><br>
                        </template>
                        <template v-if="contact.website && !contact.website.startsWith('http')">
                            <a :href="'http://'+contact.website" target="_blank">{{ contact.website }}</a><br>
                        </template>
                    </p>
                </div>
            </div>

        </div>

        <div class="embed-financial-supports-view-sidebar">

            <div v-if="templateHook('financialSupportLogo', financialSupport)"
                 v-html="templateHook('financialSupportLogo', financialSupport)"></div>

            <template v-else-if="translateField(financialSupport, 'logo', locale)">
                <div class="embed-financial-supports-view-sidebar-image" v-for="image in [translateField(financialSupport, 'logo', locale)]">
                    <img :src="$env.HOST+'/api/v1/files/view/'+ image.id +'.' + image.extension" alt="">
                </div>
            </template>

            <div class="embed-financial-supports-view-sidebar-section"
                 v-if="translateField(financialSupport, 'links', locale)?.length">
                <h3>{{ $t('Mehr Informationen', locale) }}</h3>
                <p>
                    <template v-for="(link, index) in translateField(financialSupport, 'links', locale)">
                        <a :href="link.value"
                           target="_blank">
                            {{ link.label || 'Link '+(index+1) }}
                        </a><br>
                    </template>
                </p>
            </div>

            <div class="embed-financial-supports-view-sidebar-section"
                      v-if="financialSupport.authorities?.length">
                <h3>{{ $t('Förderstelle', locale) }}</h3>
                <p>
                    <template v-for="authority in financialSupport.authorities.map(e => getAuthorityById(e.id)).filter(e => e)">{{ translateField(authority, 'name', locale) }}<br></template>
                </p>
            </div>

            <div class="embed-financial-supports-view-sidebar-section"
                 v-if="financialSupport.beneficiaries?.length">
                <h3>{{ $t('Begünstigte', locale) }}</h3>
                <p>
                    <template v-for="beneficiary in financialSupport.beneficiaries.map(e => getAuthorityById(e.id)).filter(e => e)">{{ translateField(beneficiary, 'name', locale) }}<br></template>
                </p>
            </div>

            <div class="embed-financial-supports-view-sidebar-section"
                 v-if="financialSupport.topics?.length">
                <h3>{{ $t('Thema', locale) }}</h3>
                <p>
                    <template v-for="topic in financialSupport.topics.map(e => getTopicById(e.id)).filter(e => e)">{{ translateField(topic, 'name', locale) }}<br></template>
                </p>
            </div>

            <div class="embed-financial-supports-view-sidebar-section"
                 v-if="financialSupport.projectTypes?.length">
                <h3>{{ $t('Projekttyp', locale) }}</h3>
                <p>
                    <template v-for="projectType in financialSupport.projectTypes.map(e => getProjectTypeById(e.id)).filter(e => e)">{{ translateField(projectType, 'name', locale) }}<br></template>
                </p>
            </div>

            <div class="embed-financial-supports-view-sidebar-section"
                 v-if="financialSupport.instruments?.length">
                <h3>{{ $t('Unterstützungsarten', locale) }}</h3>
                <p>
                    <template v-for="instrument in financialSupport.instruments.map(e => getInstrumentById(e.id)).filter(e => e)">{{ translateField(instrument, 'name', locale) }}<br></template>
                </p>
            </div>

            <div class="embed-financial-supports-view-sidebar-section"
                 v-if="financialSupport.geographicRegions?.length">
                <h3>{{ $t('Geographische Region', locale) }}</h3>
                <p>
                    <template v-for="geographicRegion in financialSupport.geographicRegions.map(e => getGeographicRegionById(e.id)).filter(e => e)">{{ translateField(geographicRegion, 'name', locale) }}<br></template>
                </p>
            </div>

            <div class="embed-financial-supports-view-sidebar-section">
                <a :href="'https://tools.regiosuisse.ch/api/v1/financial-supports/export/'+financialSupport.id+'-'+locale+'.pdf'"
                   class="embed-financial-supports-button"
                   target="_blank">Drucken</a>
            </div>

        </div>

        <transition name="embed-financial-supports-view-lightbox" mode="out-in">
        
            <div class="embed-financial-supports-view-lightbox" v-if="lightboxImage" @click="clickHideImage()">

                <div class="embed-financial-supports-view-lightbox-content"
                     :style="{backgroundImage: 'url('+$env.HOST+'/api/v1/files/view/'+ lightboxImage.id +'.' + lightboxImage.extension+')'}">
                </div>

                <div class="embed-financial-supports-view-lightbox-description" @click.stop v-if="lightboxImage.description || lightboxImage.copyright">
                    <template v-if="lightboxImage.description">{{ lightboxImage.description }}</template>
                    <template v-if="lightboxImage.description && lightboxImage.copyright"> | </template>
                    <template v-if="lightboxImage.copyright">© {{ lightboxImage.copyright }}</template>
                </div>

                <a class="embed-financial-supports-view-lightbox-prev" @click.stop="clickPrevImage()">
                    <span class="embed-financial-supports-view-lightbox-prev-icon"></span>
                </a>

                <a class="embed-financial-supports-view-lightbox-next" @click.stop="clickNextImage()">
                    <span class="embed-financial-supports-view-lightbox-next-icon"></span>
                </a>

            </div>

        </transition>

    </div>

</template>

<script>

import {mapGetters, mapState} from 'vuex';
import { translateField } from '../utils/filters';

export default {

    data() {
        return {
            lightboxImage: null,
        };
    },

    props: {
        locale: {
            type: String,
            default: 'de',
            required: false,
        },
        financialSupport: {
            type: Object,
            required: true,
        },
    },

    emits: [
        'clickClose',
    ],

    computed: {
        ...mapState({
            authorities: state => state.authorities.all,
            projectTypes: state => state.projectTypes.all,
            beneficiaries: state => state.beneficiaries.all,
            topics: state => state.topics.all,
            instruments: state => state.instruments.all,
            geographicRegions: state => state.geographicRegions.all,
            states: state => state.states.all,
        }),
        ...mapGetters({
            getAuthorityById: 'authorities/getById',
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

        templateHook(name, ...params) {
            if(this?.$clientOptions?.templateHooks?.[name]) {
                return this.$clientOptions.templateHooks[name](this, ...params);
            }

            return null;
        },

        clickClose() {
            this.$emit('clickClose');
        },

        clickShowImage(image) {
            this.lightboxImage = image;
        },

        clickHideImage() {
            this.lightboxImage = null;
        },

        clickPrevImage() {
            let images = this.translateField(this.financialSupport, 'images', this.locale);
            let index = images.findIndex(i => i.id === this.lightboxImage.id);

            this.lightboxImage = images[index-1] || images[images.length-1];
        },

        clickNextImage() {
            let images = this.translateField(this.financialSupport, 'images', this.locale);
            let index = images.findIndex(i => i.id === this.lightboxImage.id);

            this.lightboxImage = images[index+1] || images[0];
        },

    },

    created() {

    },

};

</script>
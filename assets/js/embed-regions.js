import '../styles/embed-regions.scss';

import { createApp } from 'vue';
import env from './utils/env';
import i18n from './common/i18n';
import helpers from './utils/helpers';
import store from './store';

import EmbedRegions from './components/EmbedRegions';
import EmbedRegionsWirkungsmessung from "./components/EmbedRegionsWirkungsmessung.vue";

const app = createApp(EmbedRegions);

app.use(env);
app.use(i18n);
app.use(helpers);
app.use(store);

const init = (selector, clientOptions = {}) => {

    app.use({
        install: (app, options) => {
            app.config.globalProperties.$clientOptions = {
                ...clientOptions,
            };
        }
    });

    app.mount(selector);

};

window[process.env.INSTANCE_ID+'Regions'] = init;

// temp

window[process.env.INSTANCE_ID+'RegionsWirkungsmessung'] = (selector, clientOptions = {}) => {

    const app = createApp(EmbedRegionsWirkungsmessung);

    app.use(env);
    app.use(i18n);
    app.use(helpers);
    app.use(store);

    app.use({
        install: (app, options) => {
            app.config.globalProperties.$clientOptions = {
                ...clientOptions,
            };
        }
    });

    app.mount(selector);

};
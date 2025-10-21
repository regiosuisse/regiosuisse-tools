import '../styles/embed-financial-supports.scss';

import { createApp } from 'vue';
import env from './utils/env';
import i18n from './common/i18n';
import helpers from './utils/helpers';
import store from './store';

import EmbedFinancialSupports from './components/EmbedFinancialSupports';

const app = createApp(EmbedFinancialSupports);

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

    return app;

};

window[process.env.INSTANCE_ID+'FinancialSupports'] = init;
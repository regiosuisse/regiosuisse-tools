import * as axios from 'axios';

const endpoint = process.env.HOST+'/api/v1/translations';

export default {

    deepl(payload) {
        return axios.post(endpoint+'/deepl', payload);
    },

};
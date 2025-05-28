import axios from 'axios';

const endpoint = process.env.HOST+'/api/v1/events';
const inboxEndpoint = process.env.HOST+'/api/v1/inbox';

export default {
    getAll() {
        return axios.get(endpoint);
    },

    getFiltered(params) {
        return axios.get(endpoint, {
            params,
        });
    },

    get(id) {
        return axios.get(endpoint+'/'+id);
    },

    getFromInbox(inboxId) {
        return axios.get(`${inboxEndpoint}/${inboxId}`)
            .then(response => {
                let eventData = null;
                
                if (response.data.data?.event) {
                    eventData = response.data.data.event;
                } 
                else if (response.data.normalizedData) {
                    eventData = response.data.normalizedData;
                }
                
                if (!eventData) {
                    throw new Error('No event data found');
                }
                
                return eventData;
            });
    },

    create(payload) {
        return axios.post(endpoint, payload);
    },

    update(id, payload) {
        return axios.put(endpoint+'/'+id, payload);
    },

    delete(id) {
        return axios.delete(endpoint+'/'+id);
    },

    createFromEmbed(payload) {
        return axios.post(endpoint + '/embed/' + payload?.locale, payload);
    },
};
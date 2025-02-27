import api from '../../api/modules/events';

export default {
    namespaced: true,

    state: {
        all: [],
    },

    getters: {
        getById: (state) => (id) => {
            return state.all.find(e => e.id === id);
        },
    },

    mutations: {
        setAll(state, events) {
            state.all = events;
        },
    },

    actions: {
        loadAll({ commit }) {
            return api.getAll().then((response) => {
                commit('setAll', response.data);
                return response.data;
            });
        },

        loadFiltered({ commit }, params) {
            return api.getFiltered(params).then((response) => {
                return response.data;
            });
        },

        load({ commit }, id) {
            return api.get(id).then((response) => {
                return response.data;
            });
        },

        loadFromInbox({ commit }, inboxId) {
            return api.getFromInbox(inboxId).then((eventData) => {
                let data;
                if (eventData.data && eventData.data.event) {
                    data = eventData.data.event;
                } else if (eventData.event) {
                    data = eventData.event;
                } else {
                    data = eventData;
                }
                return data;
            }).catch(error => {
                throw error;
            });
        },

        create({ commit }, payload) {
            return api.create(payload).then((response) => {
                return response.data;
            });
        },

        update({ commit }, { id, payload }) {
            return api.update(id, payload).then((response) => {
                return response.data;
            });
        },

        delete({ commit }, id) {
            return api.delete(id).then((response) => {
                return response.data;
            });
        },

        createFromEmbed({ commit }, payload) {
            return api.createFromEmbed(payload).then((response) => {
                return response.data;
            });
        },
    },
};
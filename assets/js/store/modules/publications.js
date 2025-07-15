import api from '../../api';

// initial state
const state = () => ({
    all: [],
    filtered: [],
    publication: {},
});

// getters
const getters = {

    getById: (state) => (id) => {
        return state.all.find(item => item.id === id);
    },

};

// actions
const actions = {

    loadAll ({ commit }) {
        commit('loaders/showLoader', 'publications', { root: true });
        return api.publications.getAll().then((response) => {
            commit('loaders/hideLoader', 'publications', { root: true });
            commit('setAll', response.data);
            return response.data;
        });
    },

    loadFiltered ({ commit }, params) {
        commit('loaders/showLoader', 'publications', { root: true });
        return api.publications.getFiltered(params).then((response) => {
            commit('loaders/hideLoader', 'publications', { root: true });
            commit('setFiltered', response.data);
            return response.data;
        });
    },

    load ({ commit }, id) {
        commit('loaders/showLoader', 'publications/'+id, { root: true });
        return api.publications.get(id).then((response) => {
            commit('loaders/hideLoader', 'publications/'+id, { root: true });
            commit('set', response.data);
            return response.data;
        });
    },

    create ({ commit }, payload) {
        commit('loaders/showLoader', 'publications/create', { root: true });
        return api.publications.create(payload).then((response) => {
            commit('loaders/hideLoader', 'publications/create', { root: true });
            if(payload.addToInbox) {
                if(payload.inboxId) {
                    commit('inbox/update', response.data, { root: true });
                } else {
                    commit('inbox/insert', response.data, { root: true });
                }
            } else {
                commit('insert', response.data);
                commit('set', response.data);
            }
        });
    },

    update ({ commit }, { id, payload }) {
        commit('loaders/showLoader', 'publications/'+id, { root: true });
        return api.publications.update(id, payload).then((response) => {
            commit('loaders/hideLoader', 'publications/'+id, { root: true });
            if(payload.addToInbox) {
                if(payload.inboxId) {
                    commit('inbox/update', response.data, { root: true });
                } else {
                    commit('inbox/insert', response.data, { root: true });
                }
            } else {
                commit('update', response.data);
                commit('set', response.data);
            }
            return response.data;
        });
    },

    delete ({ commit }, id) {
        commit('loaders/showLoader', 'publications/'+id, { root: true });
        return api.publications.delete(id).then((response) => {
            commit('loaders/hideLoader', 'publications/'+id, { root: true });
            commit('remove', id);
        });
    },

    createFromEmbed({ commit }, payload) {
        commit('loaders/showLoader', 'publications/create', { root: true });
        return api.publications.createFromEmbed(payload).then((response) => {
            commit('loaders/hideLoader', 'publications/create', { root: true });
            commit('inbox/insert', response.data.inbox, { root: true });
            return response.data;
        });
    },

};

// mutations
const mutations = {

    setAll (state, publications) {
        state.all = publications;
    },

    setFiltered (state, filtered) {
        state.filtered = filtered;
    },

    set (state, publication) {
        if(publication) {
            publication = {
                ...publication,
                translations: typeof publication.translations === 'object' && publication.translations !== null && !Array.isArray(publication.translations) ? publication.translations : {},
            };
        }
        state.publication = publication;
    },

    insert (state, publication) {
        state.all = [...state.all, publication];
    },

    update (state, publication) {
        let existingPublication = state.all.find(p => p.id === publication.id);
        if(existingPublication) {
            state.all[state.all.indexOf(existingPublication)] = publication;
        }
    },

    remove (state, id) {
        state.all = state.all.filter((publication) => {
            return parseInt(publication.id) !== parseInt(id);
        });
    },

};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
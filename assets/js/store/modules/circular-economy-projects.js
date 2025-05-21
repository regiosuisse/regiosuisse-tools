import api from '../../api';

// initial state
const state = () => ({
    all: [],
    filtered: [],
    circularEconomyProject: {},
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
        commit('loaders/showLoader', 'circularEconomyProjects', { root: true });
        return api.circularEconomyProjects.getAll().then((response) => {
            commit('loaders/hideLoader', 'circularEconomyProjects', { root: true });
            commit('setAll', response.data);
            return response.data;
        });
    },

    loadFiltered ({ commit }, params) {
        commit('loaders/showLoader', 'circularEconomyProjects', { root: true });
        return api.circularEconomyProjects.getFiltered(params).then((response) => {
            commit('loaders/hideLoader', 'circularEconomyProjects', { root: true });
            commit('setFiltered', response.data);
            return response.data;
        });
    },

    load ({ commit }, id) {
        commit('loaders/showLoader', 'circularEconomyProjects/'+id, { root: true });
        return api.circularEconomyProjects.get(id).then((response) => {
            commit('loaders/hideLoader', 'circularEconomyProjects/'+id, { root: true });
            commit('set', response.data);
            return response.data;
        });
    },

    create ({ commit }, payload) {
        commit('loaders/showLoader', 'circularEconomyProjects/create', { root: true });
        return api.circularEconomyProjects.create(payload).then((response) => {
            commit('loaders/hideLoader', 'circularEconomyProjects/create', { root: true });
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

    update ({ commit }, payload) {
        commit('loaders/showLoader', 'circularEconomyProjects/'+payload.id, { root: true });
        return api.circularEconomyProjects.update(payload.id, payload).then((response) => {
            commit('loaders/hideLoader', 'circularEconomyProjects/'+payload.id, { root: true });
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
        });
    },

    delete ({ commit }, id) {
        commit('loaders/showLoader', 'circularEconomyProjects/'+id, { root: true });
        return api.circularEconomyProjects.delete(id).then((response) => {
            commit('loaders/hideLoader', 'circularEconomyProjects/'+id, { root: true });
            commit('remove', id);
        });
    },

};

// mutations
const mutations = {

    setAll (state, circularEconomyProjects) {
        state.all = circularEconomyProjects;
    },

    setFiltered (state, filtered) {
        state.filtered = filtered;
    },

    set (state, circularEconomyProject) {
        if(circularEconomyProject) {
            circularEconomyProject = {
                ...circularEconomyProject,
                translations: typeof circularEconomyProject.translations === 'object' && circularEconomyProject.translations !== null && !Array.isArray(circularEconomyProject.translations) ? circularEconomyProject.translations : {},
            };
        }
        state.circularEconomyProject = circularEconomyProject;
    },

    insert (state, circularEconomyProject) {
        state.all = [...state.all, circularEconomyProject];
    },

    update (state, circularEconomyProject) {
        let existingProject = state.all.find(p => p.id === circularEconomyProject.id);
        if(existingProject) {
            state.all[state.all.indexOf(existingProject)] = circularEconomyProject;
        }
        existingProject = state.filtered.find(p => p.id === circularEconomyProject.id);
        if(existingProject) {
            state.filtered[state.filtered.indexOf(existingProject)] = circularEconomyProject;
        }
    },

    remove (state, id) {
        state.all = state.all.filter((circularEconomyProject) => {
            return parseInt(circularEconomyProject.id) !== parseInt(id);
        });
        state.filtered = state.filtered.filter((circularEconomyProject) => {
            return parseInt(circularEconomyProject.id) !== parseInt(id);
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
import axios from 'axios'

const state = {
    data: {},
    loading: false,
    loading_table: false,
    buscar: "",
};
const getters = {};

const actions = {
    async getFichas({commit }, page) {
        commit('SET_LOADING_TABLE', true);
        try {
            const res = await axios.get('api/v1/super_pcr?page=' + page + '&buscar=' + state.buscar)
            if (res && res.data) {
                commit('SET_LOADING_TABLE', false);
                commit('SET_DATA', res.data);
            }
        } catch(e) {
            commit('SET_LOADING_TABLE', false);
        }
    },
};

const mutations = {
    SET_DATA(state, data) {
        state.data = data;
    },
    SET_LOADING(state, loading) {
        state.loading = loading;
    },
    SET_LOADING_TABLE(state, loading) {
        state.loading_table = loading;
    },
    SET_CURRENT_PAGE(state, page) {
        state.data.current_page = page;
    },
    SET_CRITERIO_BUSQUEDA (state, criterio) {
        state.buscar = criterio;
    }
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}

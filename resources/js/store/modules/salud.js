import axios from 'axios'

const state = {
    data: {},
    loading: false,
    buscar: "",
    paciente: "",
};
const getters = {};

const actions = {
    async getFichas({ commit }, page) {
        try {
            const res = await axios.get('api/v1/salud?page=' + page + '&buscar=' + state.buscar)
            if (res && res.data) {
                commit('SET_DATA', res.data);
            }
        } catch(e) {
            //console.log(e);
        }
    },
    async getPatientInfo({ commit }, id_paciente) {
        commit('SET_PACIENTE', "");
        try {
            const res = await axios.get('api/v1/paciente/' + id_paciente);
            commit("SET_PACIENTE", await res.data);
        } catch (err) {
            console.log(err);
        }
    },
};

const mutations = {
    SET_DATA(state, data) {
        state.data = data;
    },
    SET_PACIENTE(state, data) {
        state.paciente = data;
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

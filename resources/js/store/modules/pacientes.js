import axios from 'axios';
import router from '../../router/index'

const state = {
  data: {},
  loading: false,
  loading_table: false,
  buscar: "",
};

const getters = {
  getPacienteById: (state, getters) => (id) => {
    return getters.getPacientes.find(paciente => paciente.idpacientes === id);
  },
  getCriterioBusqueda: state => state.buscar,
  getCurrentPage: state => state.data.current_page,
  getPacientes: state => state.data.data,
};

const actions = {
  async getFichas({commit, dispatch, getters }, page) {
    const config = {
      params: {
        page: page,
        buscar: getters.getCriterioBusqueda
      }
    }
    commit('SET_LOADING_TABLE', true);
    try {
      const res = await axios.get(`api/v1/paciente`, config)
      commit('SET_LOADING_TABLE', false);
      commit('SET_DATA', await res.data);
    } catch(e) {
      commit('SET_LOADING_TABLE', false);
    }
  },
  async updatePaciente({commit}, paciente) {
    try {
      const res = await axios.put(`api/v1/paciente/${paciente.idpacientes}`, paciente)
      commit('SHOW_SUCCESS_SNACKBAR', await res.data.message, {root: true})
      await router.push('/pacientes')
    } catch (err) {
      commit('SHOW_ERROR_SNACKBAR', err.response.data.message, {root: true});
    }
  },
  async deletePaciente({commit}, paciente){
    try {
      const res = await axios.delete(`api/v1/paciente/${paciente.idpacientes}`)
      await commit('ELIMINAR_PACIENTE', paciente);
      commit('SHOW_SUCCESS_SNACKBAR', await res.data.message, {root: true});
    } catch (err) {
      commit('SHOW_ERROR_SNACKBAR', err.response.data.message, {root: true});
    }
  }
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
  },
  ELIMINAR_PACIENTE(state, paciente) {
    const pacientes = state.data.data;
    if (pacientes.length > 0) {
      const idx = pacientes.indexOf(paciente);
      if(idx >= 0) pacientes.splice(idx, 1);
    }
  },
  ACTUALIZAR_PACIENTE(state, { paciente, nuevoPaciente }) {
    const pacientes = state.data.data;
    if (pacientes.length > 0) {
      const idx = pacientes.indexOf(paciente);
      if(idx >= 0) pacientes.splice(idx, 1, nuevoPaciente);
    }
  }
};

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations
}

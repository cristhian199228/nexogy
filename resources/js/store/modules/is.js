import axios from 'axios'
import moment from 'moment'

const state = {
  data: {},
  filtros: {
    empresa: null,
    buscar: null,
    fechas: [
      moment().format('YYYY-MM-DD'),
      moment().format('YYYY-MM-DD')
    ],
    resultado: null,
    prs: null,
    sede: null,
  },
  dialog: false,
  dialog_curva: false,
  dialog_correo: false,
  id_pcr: null,
  loading: false,
}

const getters = {
  fichas: state => state.data.data,
  filtroFecha: state => state.filtros.fechas,
  filtroBuscar: state => state.filtros.buscar,
  filtroEmpresa: state => state.filtros.empresa,
  currentPage: state => state.data.current_page,
  idPcr: state => state.id_pcr,
}

const actions = {
  async getFichas({commit, getters}, page) {
    const config = {
      params: {
        page,
        fecha_inicio: getters.filtroFecha[0],
        fecha_fin: getters.filtroFecha[1],
        buscar: getters.filtroBuscar,
        empresa: getters.filtroEmpresa,
        resultado: state.filtros.resultado,
        sede: state.filtros.sede,
        ps: state.filtros.prs,
      }
    }
    try {
      const res = await axios.get('api/v1/is/getData', config)
      commit('SET_DATA', await res.data)
    } catch (e) {
      console.error(await e.response.data)
    }
  },
  async guardarReevaluacionPcr({commit, dispatch, rootGetters, getters}, params) {
    const data = {
      idpcr_prueba_molecular: params.id,
      dias_bloqueo: params.dias_bloqueo,
    }
    try {
      const res = await axios.post('api/v1/is/pcr/guardar', data)
      commit('SHOW_SUCCESS_SNACKBAR', await res.data.message, {root: true});
      dispatch('getFichas', getters.currentPage)
    } catch (e) {
      commit('SHOW_ERROR_SNACKBAR', await e.response.data.message, {root: true});
    }
  },
  async guardarReevaluacionPrs({commit, dispatch, rootGetters, getters}, params) {
    const data = {
      idpruebaserologicas: params.id,
      dias_bloqueo: params.dias_bloqueo,
    }
    try {
      const res = await axios.post('api/v1/is/prs/guardar', data)
      commit('SHOW_SUCCESS_SNACKBAR', await res.data.message, {root: true});
      dispatch('getFichas', getters.currentPage)
    } catch (e) {
      commit('SHOW_ERROR_SNACKBAR', await e.response.data.message, {root: true});
    }
  },
  async sendMail({commit}, idficha_paciente) {
    const data = {
      idficha_paciente,
    }
    commit('SET_LOADING', true)
    try {
      const res = await axios.post('api/v1/is/sendMail', data)
      commit('SHOW_SUCCESS_SNACKBAR', await res.data.message, {root: true})
      commit('SET_LOADING', false)
      commit('SHOW_DIALOG_ENVIAR_CORREO', false)
    } catch (e) {
      commit('SHOW_ERROR_SNACKBAR', await e.response.data.message, {root: true})
      commit('SET_LOADING', false)
    }
  }
}

const mutations = {
  SET_DATA (state, data) {
    state.data = data
  },
  SET_FILTRO_FECHA (state, fechas){
    state.filtros.fechas = fechas
  },
  SET_FILTRO_EMPRESA (state, emp){
    state.filtros.empresa = emp
  },
  SET_FILTRO_RESULTADO(state, resultado){
    state.filtros.resultado = resultado;
  },
  SET_FILTRO_RESULTADO_PRS(state, resultado){
    state.filtros.prs = resultado;
  },
  SHOW_DIALOG_GUARDAR_REEVALUACION(state, show) {
    state.dialog = show
  },
  SET_FILTRO_SEDE(state, sede){
    state.filtros.sede = sede;
  },
  SHOW_DIALOG_ENVIAR_CORREO(state, show) {
    state.dialog_correo = show
  },
  SHOW_DIALOG_CURVA(state, show) {
    state.dialog_curva = show
  },
  SET_ID_PCR(state, id) {
    state.id_pcr = id
  },
  SET_CURRENT_PAGE(state, page) {
    state.data.current_page = page
  },
  SET_CRITERIO_BUSQUEDA (state, criterio) {
    state.filtros.buscar = criterio
  },
  SET_LOADING (state, loading) {
    state.loading = loading
  }
}

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations
}

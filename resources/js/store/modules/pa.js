import axios from 'axios'

const state = {
  data: {},
  loading: false,
  buscar: "",
  dialog: false,
  resultados: ['NEG','POS','INVALIDO','INDETERMINADO'],
  colores: ['green','red','orange','grey'],
  id_ficha: null,
};

const getters = {
  criterioBusqueda: state => state.buscar,
  fichas: state => state.data.data,
  currentPage: state => state.data.current_page,
  resultadoPa: state => res => state.resultados[res],
  colorPa: state => res => `${state.colores[res]} darken-2`
}

const actions = {
  async getFichas({commit, getters, rootGetters}, page) {
    const config = {
      params: {
        page,
        id_estacion: rootGetters.getEstacion,
        buscar: getters.criterioBusqueda,
      }
    }
    if (rootGetters.getEstacion) {
      try {
        const res = await axios.get('api/v1/controladorPa', config)
        commit('SET_DATA', await res.data)
      } catch (e) {
        console.error(await e.response.data)
      }
    }
  },
  async store({commit, dispatch, getters}, idficha_paciente) {
    const params = {
      idficha_paciente,
    }
    try {
      const res = await axios.post('api/v1/pa', params)
      commit('SHOW_SUCCESS_SNACKBAR', await res.data.message, {root: true})
      dispatch('getFichas', getters.currentPage)
    } catch (e) {
      commit('SHOW_ERROR_SNACKBAR', await e.response.data.message, {root: true})
    }
  },
  async start({commit, dispatch, getters}, id_pa) {
    try {
      const res = await axios.put(`api/v1/pa/${id_pa}/start`)
      commit('SHOW_SUCCESS_SNACKBAR', await res.data.message, {root: true})
      dispatch('getFichas', getters.currentPage)
    } catch (e) {
      commit('SHOW_ERROR_SNACKBAR', await e.response.data.message, {root: true})
    }
  },

  async delete({commit, dispatch, getters}, id_pa) {
    try {
      const res = await axios.delete(`api/v1/pa/${id_pa}`)
      commit('SHOW_SUCCESS_SNACKBAR', await res.data.message, {root: true})
      dispatch('getFichas', getters.currentPage)
    } catch (e) {
      commit('SHOW_ERROR_SNACKBAR', await e.response.data.message, {root: true})
    }
  },

  async update({commit, dispatch, getters}, pa) {
    for (const key of Object.keys(pa)) {

    }
    try {
      const res = await axios.put(`api/v1/pa/${pa.id}`, pa)
      commit('SHOW_SUCCESS_SNACKBAR', await res.data.message, {root: true})
      commit('SHOW_SAVE_PRUEBA_ANTIGENA_DIALOG', false)
      return dispatch('getFichas', getters.currentPage)
    } catch (e) {
      commit('SHOW_ERROR_SNACKBAR', await e.response.data.message, {root: true})
    }
  },

  uploadA3({commit, dispatch, getters, rootState}) {
    const config = {
      headers: { "content-type": "multipart/form-data" },
    };
    const formData = new FormData();
    formData.append("file", rootState.photo);
    formData.append("id_ficha", state.id_ficha);
    commit('SET_LOADING', true, {root: true});

    axios.post('api/v1/a3', formData, config)
      .then(res => {
        if(res && res.data) {
          commit('SET_LOADING', false, {root: true});
          commit('SHOW_DIALOG_UPLOAD_FOTO', false, {root: true});
          commit('SHOW_SUCCESS_SNACKBAR', "Se subió la foto correctamente", {root: true});
          dispatch('getFichas', getters.currentPage)
        }
      })
      .catch(err => {
        if(err) {
          commit('SET_LOADING', false, {root: true});
          commit('SHOW_ERROR_SNACKBAR', getters.currentPage, {root: true});
        }
      });
  },

  deleteA3({ commit, dispatch, getters}, a3) {
    const config = {
      data: { path: a3.path }
    };
    commit('SET_LOADING', true);
    axios.delete('api/v1/a3/'+ a3.idanexotres, config)
      .then(res => {
        if(res && res.data) {
          commit('SHOW_SUCCESS_SNACKBAR', "Se eliminó la foto correctamente", {root: true});
          dispatch('getFichas', getters.currentPage);
        }
      })
      .catch(err => {
        if(err && err.response) {
          commit('SHOW_ERROR_SNACKBAR', err.response.data.message, {root: true});
        }
      })
  },

  async storeWP({ commit, dispatch, getters }, id) {
    try {
      const res = await axios.post('api/v1/wp/ag', {id})
      commit('SHOW_SUCCESS_SNACKBAR', await res.data.message, {root: true})
      dispatch('getFichas', getters.currentPage)
    } catch (e) {
      commit('SHOW_ERROR_SNACKBAR', await e.response.data.message, {root: true});
    }
  },
}

const mutations = {
  SET_DATA(state, data) {
    state.data = data;
  },
  SET_LOADING(state, loading) {
    state.loading = loading;
  },
  SET_CURRENT_PAGE(state, page) {
    state.data.current_page = page;
  },
  SET_CRITERIO_BUSQUEDA (state, criterio) {
    state.buscar = criterio;
  },
  SHOW_SAVE_PRUEBA_ANTIGENA_DIALOG (state, show) {
    state.dialog = show
  },
  SET_ID_FICHA(state, id) {
    state.id_ficha = id;
  },
};

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations
}

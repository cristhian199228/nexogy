import axios from 'axios'

const state = {
    data: {},
    id_ficha: null,
    id_ps: null,
    dialog: {
        ps: {
            update: false,
        },
    },
    loading: false,
    buscar: "",
};

const getters = {};

const actions = {
    async getFichas({commit, rootState }, page) {
        if (rootState.id_estacion) {
            try {
                const res = await axios.get('api/v1/controlador?estacion=' + rootState.id_estacion + '&page=' + page + '&buscar=' + state.buscar)
                if (res && res.data) {
                    commit('SET_DATA', res.data);
                }
            } catch(e) {
                console.error(e)
                commit('SET_LOADING_TABLE', false);
            }
        }
    },
    storePS({ commit, dispatch, rootState }, ficha) {
        axios.post('api/v1/ps', {
            idficha_paciente: ficha.idficha_paciente,
            id_paciente: ficha.id_paciente,
        })
          .then(res => {
              if (res && res.data) {
                  commit('SHOW_SUCCESS_SNACKBAR', "Se guardó correctamente", {root: true});
                  dispatch('getFichas', state.data.current_page);
              }
          })
          .catch(err => {
              if (err && err.response) {
                  commit('SHOW_ERROR_SNACKBAR', err.response.data.message, {root: true});
              }
          })
    },
    async storeWP({ commit, dispatch, rootState }, prueba) {
        const data = {
            idpruebaserologicas: prueba.idpruebaserologicas,
        }
        try {
            const res = await axios.post('api/v1/wp', data)
            commit('SHOW_SUCCESS_SNACKBAR', await res.data.message, {root: true})
            dispatch('getFichas', state.data.current_page)
        }catch (e) {
            commit('SHOW_ERROR_SNACKBAR', e.response.data.message, {root: true});
        }
    },
    startPS({ commit, dispatch }, id_ps) {
        axios.put(`api/v1/ps/${id_ps}/start`)
          .then(res => {
              if (res && res.data) {
                  commit('SHOW_SUCCESS_SNACKBAR', "Se inició la prueba correctamente", {root: true});
                  dispatch('getFichas', state.data.current_page);
              }
          })
          .catch(err => {
              if (err && err.response) {
                  commit('SHOW_ERROR_SNACKBAR', err.response.data.message, {root: true});
              }
          })
    },
    updatePS({ commit, dispatch }, ps) {
        for (const key of Object.keys(ps)) {
            if (key !== 'condicion_riesgo_detalle' && key !== 'ps_otro' &&
              key !== 'fecha_positivo_anterior' && key !== 'lugar_positivo_anterior') {
                ps[key] = Number(ps[key]);
            }
        }
        axios.put(`api/v1/ps/${state.id_ps}`, {
            ps_llamada_113: ps.ps_llamada_113,
            ps_de_eess: ps.ps_de_eess,
            ps_contactocasocon: ps.ps_contactocasocon,
            ps_contactocasosos: ps.ps_contactocasosos,
            ps_personaext: ps.ps_personaext,
            ps_personalsalud: ps.ps_personalsalud,
            ps_otro: ps.ps_otro,
            p1_react1gm: ps.p1_react1gm,
            p1_reactigg: ps.p1_reactigg,
            p1_reactigm_igg: ps.p1_reactigm_igg,
            no_reactivo: ps.no_reactivo,
            invalido: ps.invalido,
            ccs: ps.ccs,
            condicion_riesgo: ps.condicion_riesgo,
            condicion_riesgo_detalle: ps.condicion_riesgo_detalle,
            p1_positivo_recuperado: ps.p1_positivo_recuperado,
            p1_marca: ps.p1_marca,
            p1_positivo_persistente: ps.p1_positivo_persistente,
            positivo_anterior: ps.positivo_anterior,
            fecha_positivo_anterior: ps.fecha_positivo_anterior,
            lugar_positivo_anterior: ps.lugar_positivo_anterior,
            p1_positivo_vacunado: ps.p1_positivo_vacunado,
        })
          .then(res => {
              if (res && res.data) {
                  commit('SHOW_SUCCESS_SNACKBAR', "Se guardó correctamente", {root: true});
                  commit('SHOW_DIALOG_UPDATE_PS', false);
                  dispatch('getFichas', state.data.current_page);
              }
          })
          .catch(err => {
              if (err && err.response) {
                  commit('SHOW_ERROR_SNACKBAR', err.response.data.message, {root: true});
              }
          })
    },
    deletePS({ commit, dispatch }, id_ps) {
        axios.delete('api/v1/ps/' + id_ps)
          .then(res => {
              if (res && res.data) {
                  commit('SHOW_SUCCESS_SNACKBAR', "Se eliminó correctamente", {root: true});
                  dispatch('getFichas', state.data.current_page);
              }
          })
          .catch(err => {
              if (err && err.response) {
                  commit('SHOW_ERROR_SNACKBAR', err.response.data.message, {root: true});
              }
          })
    },
    uploadA3({commit, dispatch, rootState}) {
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
                  dispatch('getFichas', state.data.current_page);
              }
          })
          .catch(err => {
              if(err) {
                  commit('SET_LOADING', false, {root: true});
                  commit('SHOW_ERROR_SNACKBAR', err.response.data.message, {root: true});
              }
          });
    },
    deleteA3({ commit, dispatch}, a3) {
        const config = {
            data: { path: a3.path }
        };
        commit('SET_LOADING', true);
        axios.delete('api/v1/a3/'+ a3.idanexotres, config)
          .then(res => {
              if(res && res.data) {
                  commit('SHOW_SUCCESS_SNACKBAR', "Se eliminó la foto correctamente", {root: true});
                  dispatch('getFichas', state.data.current_page);
              }
          })
          .catch(err => {
              if(err && err.response) {
                  commit('SHOW_ERROR_SNACKBAR', err.response.data.message, {root: true});
              }
          })
    },
    async updateDiasBloqueo({commit}, ps) {
        try {
            await axios.put(`/api/v1/ps/updateDiasBloqueo/${ps.idpruebaserologicas}`, ps)
            commit('SHOW_SUCCESS_SNACKBAR', "Se guardó correctamente", {root: true})
        } catch (err) {
            commit('SHOW_ERROR_SNACKBAR', err.response.data.message, {root: true})
        }
    },

    async enviarWpPrueba({commit}, data) {
        try {
            const res = await axios.post('api/v1/wp/prueba', data)
            commit('SHOW_SUCCESS_SNACKBAR', await res.data.message, {root: true})
        } catch (err) {
            commit('SHOW_ERROR_SNACKBAR', await err.response.data.message, {root: true})
        }
    }
};

const mutations = {
    SET_DATA(state, data) {
        state.data = data;
    },
    SET_ID_FICHA(state, id) {
        state.id_ficha = id;
    },
    SET_ID_PS(state, id) {
        state.id_ps = id;
    },
    SET_LOADING(state, loading) {
        state.loading = loading;
    },
    SHOW_DIALOG_UPDATE_PS (state, show) {
        state.dialog.ps.update = show;
    },
    SET_CURRENT_PAGE(state, page) {
        state.data.current_page = page;
    },
    SET_CRITERIO_BUSQUEDA (state, criterio) {
        state.buscar = criterio;
    },
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}

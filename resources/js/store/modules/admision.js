import axios from 'axios'
import router from '../../router'

const state = {
    data: {},
    dialog: {
        upload: {
            dc: false,
            ae: false,
            temp: false,
        },
        edit: {
            dc: false,
            ae: false,
            temp: false,
        }
    },
    id_ficha: null,
    id_dc: null,
    id_ae: null,
    loading: false,
    loading_table: false,
    buscar: "",
};

const getters = {
    currentPage: state => state.data.current_page
};

const actions = {
    async getFichas({commit, rootState }, page) {
        if (rootState.id_estacion) {
            commit('SET_LOADING_TABLE', true);
            try {
                const res = await axios.get('api/v1/admision?estacion=' + rootState.id_estacion + '&page=' + page + '&buscar=' + state.buscar)
                if (res && res.data) {
                    commit('SET_LOADING_TABLE', false);
                    commit('SET_DATA', res.data);
                }
            } catch(e) {
                commit('SET_LOADING_TABLE', false);
            }
        }
    },

    storePaciente({ commit }, params) {
        commit('SET_LOADING', true);
        axios.post('api/v1/paciente', {
            tipo_doc: params.tipo_documento,
            nro_doc: params.numero_documento,
            nombres: params.nombres,
            ap: params.apellido_paterno,
            am: params.apellido_materno,
            fecha_nac: params.fecha_nacimiento,
            sexo: params.sexo,
            id_dep: params.id_departamento,
            id_pro: params.id_provincia,
            id_dis: params.id_distrito,
            id_empresa: params.id_empresa,
            puesto: params.puesto,
            direccion: params.direccion,
            correo: params.correo,
            celular: params.celular,
        })
          .then( res => {
              if (res && res.data) {
                  commit('SET_LOADING', false);
                  commit('SHOW_SUCCESS_SNACKBAR', "Se guardó correctamente", {root: true});
                  router.push({ name: 'nueva_ficha'});
              }
          })
          .catch(e => {
              if (e && e.response) {
                  commit('SET_LOADING', false);
                  commit('SHOW_ERROR_SNACKBAR', e.response.data.message, {root: true});
              }
          })
    },

    async storeFicha({ commit, dispatch, rootState }, params) {
        commit('SET_LOADING', true);
        params.id_estacion = rootState.id_estacion;
        try {
            const res = await axios.post('api/v1/ficha', params)
            commit('SET_LOADING', false);
            commit('SHOW_SUCCESS_SNACKBAR', await res.data.message, {root: true});
            await router.push({ name: 'tabla_admision'});
        } catch(e) {
            commit('SET_LOADING', false);
            commit('SHOW_ERROR_SNACKBAR', await e.response.data.message, {root: true});
        }
    },

    updateTurno({ commit, dispatch }, ficha)  {
        let turno = ficha.turno;
        if (turno === 1) turno = 2;
        else turno = 1;
        axios.put(`api/v1/ficha/${ficha.idficha_paciente}/turno`, { turno })
          .then(res => {
              if (res && res.data) {
                  commit('SHOW_SUCCESS_SNACKBAR', res.data.message, {root: true});
                  dispatch('getFichas', state.data.current_page);
              }
          })
          .catch(err => {
              if (err && err.response) {
                  commit('SHOW_ERROR_SNACKBAR', err.response.data.message, {root: true});
              }
          })
    },

    uploadCT({commit, dispatch, rootState }) {
        const config = {
            headers: { "content-type": "multipart/form-data" },
        };
        const formData = new FormData();
        formData.append("file", rootState.photo);
        formData.append("id_ficha", state.id_ficha);
        commit('SET_LOADING', true, {root: true});

        axios.post('api/v1/dj', formData, config)
          .then(res => {
              if(res && res.data) {
                  commit('SET_LOADING', false, {root: true});
                  commit('SHOW_DIALOG_UPLOAD_FOTO', false, {root: true});
                  commit('SHOW_SUCCESS_SNACKBAR', "Foto subida correctamente", {root: true});
                  dispatch('getFichas', state.data.current_page);
              }
          })
          .catch(err => {
              if(err && err.response) {
                  commit('SET_LOADING', false, {root: true});
                  commit('SHOW_ERROR_SNACKBAR', err.response.data.message, {root: true});
              }
          });
    },
    uploadCI({commit, dispatch, rootState}) {
        const config = {
            headers: { "content-type": "multipart/form-data" },
        };
        const formData = new FormData();
        formData.append("file", rootState.photo);
        formData.append("id_ficha", state.id_ficha);
        commit('SET_LOADING', true, {root: true});

        axios.post('api/v1/ci', formData, config)
          .then(res => {
              if(res && res.data) {
                  commit('SET_LOADING', false, {root: true});
                  commit('SHOW_DIALOG_UPLOAD_FOTO', false, {root: true});
                  commit('SHOW_SUCCESS_SNACKBAR', "Foto subida correctamente", {root: true});
                  dispatch('getFichas', state.data.current_page);
              }
          })
          .catch( err => {
              if(err && err.response) {
                  commit('SET_LOADING', false, {root: true});
                  commit('SHOW_ERROR_SNACKBAR', err.response.data.message, {root: true});
              }
          });
    },
    deleteCT({ commit, dispatch}, dj) {
        const config = {
            data: { path: dj.path }
        };
        axios.delete('api/v1/dj/'+ dj.iddeclaracionesjuradas, config)
          .then(res => {
              if(res && res.data) {
                  commit('SHOW_SUCCESS_SNACKBAR', "Se eliminó correctamente", {root: true});
                  dispatch('getFichas', state.data.current_page);
              }
          })
          .catch(err => {
              if(err && err.response) {
                  commit('SHOW_ERROR_SNACKBAR', err.response.data.message, {root: true});
              }
          })
    },
    deleteCI({ commit, dispatch}, ci) {
        const config = {
            data: { path: ci.path }
        };
        axios.delete(`api/v1/ci/${ci.idconsentimientoinformados}`, config)
          .then(res => {
              if(res && res.data) {
                  commit('SHOW_SUCCESS_SNACKBAR', "Se eliminó correctamente", {root: true});
                  dispatch('getFichas', state.data.current_page);
              }
          })
          .catch(err => {
              if(err && err.response) {
                  commit('SHOW_ERROR_SNACKBAR', err.response.data.message, {root: true});
              }
          })
    },
    storeDC({}, dc) {
        dc.idfichapacientes = state.id_ficha
        return axios.post('api/v1/dc', dc)
    },
    updateDC({}, dc) {
        return axios.put(`api/v1/dc/${dc.iddatoclinicos}`, dc)
    },
    deleteDC({ commit, dispatch }, id_dc) {
        axios.delete('api/v1/dc/' + id_dc)
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
    storeAE({}, ae) {
        ae.idfichapacientes = state.id_ficha
        return axios.post('api/v1/ae', ae)
    },
    updateAE({}, ae) {
        return axios.put(`api/v1/ae/${ae.idaepidemologicos}`, ae)
    },
    deleteAE({ commit, dispatch }, id_ae) {
        axios.delete('api/v1/ae/' + id_ae)
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
    storeTemp({ commit, dispatch, rootState } , temp) {
        commit('SET_LOADING', true);
        axios.post('api/v1/temp', {
            idficha_paciente: state.id_ficha,
            temperatura: temp,
        })
          .then(res => {
              if (res && res.data) {
                  commit('SET_LOADING', false);
                  commit('SHOW_SUCCESS_SNACKBAR', "Se guardó correctamente", {root: true});
                  commit('SHOW_DIALOG_STORE_TEMP', false);
                  dispatch('getFichas', state.data.current_page);
              }
          })
          .catch(err => {
              if (err && err.response) {
                  commit('SET_LOADING', false);
                  commit('SHOW_ERROR_SNACKBAR', err.response.data.message, {root: true});
              }
          })
    },
    editTemp({ commit, dispatch }, temp) {
        commit('SET_LOADING', true);
        axios.put('api/v1/temp/' + state.id_temp, {
            temperatura: temp
        })
          .then(res => {
              if (res && res.data) {
                  commit('SET_LOADING', false);
                  commit('SHOW_SUCCESS_SNACKBAR', "Se guardó correctamente", {root: true});
                  commit('SHOW_DIALOG_EDIT_TEMP', false);
                  dispatch('getFichas', state.data.current_page);
              }
          })
          .catch(err => {
              if (err && err.response) {
                  commit('SET_LOADING', false);
                  commit('SHOW_ERROR_SNACKBAR', err.response.data.message, {root: true});
              }
          })
    },
    deleteTemp({ commit, dispatch }, id_temp) {
        axios.delete('api/v1/temp/' + id_temp)
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
    async saveSignature({commit}, data) {
        try {
            const res = await axios.post('api/v1/firma', data)
            commit('SHOW_SUCCESS_SNACKBAR', await res.data.message, {root: true})
            await router.push('/admision')
        } catch (e) {
            commit('SHOW_ERROR_SNACKBAR', await e.response.data.message, {root: true})
        }
    }
};

const mutations = {
    SET_DATA(state, data) {
        state.data = data;
    },
    SHOW_DIALOG_STORE_DC(state, show) {
        state.dialog.upload.dc = show;
    },
    SHOW_DIALOG_STORE_AE(state, show) {
        state.dialog.upload.ae = show;
    },
    SHOW_DIALOG_STORE_TEMP(state, show) {
        state.dialog.upload.temp = show;
    },
    SHOW_DIALOG_EDIT_DC(state, show) {
        state.dialog.edit.dc = show;
    },
    SHOW_DIALOG_EDIT_AE(state, show) {
        state.dialog.edit.ae = show;
    },
    SHOW_DIALOG_EDIT_TEMP(state, show) {
        state.dialog.edit.temp = show;
    },
    SET_ID_FICHA(state, id) {
        state.id_ficha = id;
    },
    SET_ID_DC(state, id) {
        state.id_dc = id;
    },
    SET_ID_AE(state, id) {
        state.id_ae = id;
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
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}
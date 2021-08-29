import axios from 'axios'

const state = {
    data: {},
    id_foto_fi: null,
    id_fi: null,
    dialog: {
        barcode: false,
    },
    loading: false,
    loading_table: false,
    buscar: "",
};
const getters = {};
const actions = {
    async getFichas({commit, rootState }, page) {
        if (rootState.id_estacion) {
            commit('SET_LOADING_TABLE', true);
            try {
                const res = await axios.get('api/v1/pcr?estacion=' + rootState.id_estacion + '&page=' + page + '&buscar=' + state.buscar)
                if (res && res.data) {
                    commit('SET_LOADING_TABLE', false);
                    commit('SET_DATA', res.data);
                }
            } catch(e) {
                commit('SET_LOADING_TABLE', false);
            }
        }
    },

    storePM({ commit, dispatch, rootState }, ficha) {
        axios.post('api/v1/pm', ficha)
            .then(res => {
                if (res && res.data) {
                    commit('SHOW_SUCCESS_SNACKBAR', res.data.message, {root: true});
                    dispatch('getFichas', state.data.current_page);
                }
            })
            .catch(err => {
                if (err && err.response) {
                    commit('SHOW_ERROR_SNACKBAR', err.response.data.message, {root: true});
                    dispatch('getFichas', state.data.current_page);
                }
            })
    },

    finishPM({ commit, dispatch, rootState }, id_pcr) {
        axios.put('api/v1/pm/' + id_pcr + '/finish')
            .then(res => {
                if (res && res.data) {
                    commit('SHOW_SUCCESS_SNACKBAR', res.data.message, {root: true});
                    dispatch('getFichas', state.data.current_page);
                }
            })
            .catch(err => {
                if (err && err.response) {
                    commit('SHOW_ERROR_SNACKBAR', err.response.data.message, {root: true});
                    dispatch('getFichas', state.data.current_page);
                }
            })
    },

    uploadF1({commit, dispatch, rootState}) {
        const config = {
            headers: { "content-type": "multipart/form-data" },
        };
        const formData = new FormData();
        formData.append("file", rootState.photo);
        formData.append("idinv_ficha", state.id_fi);
        commit('SET_LOADING', true, {root: true});

        axios.post('api/v1/fi', formData, config)
            .then(res => {
                if(res && res.data) {
                    commit('SET_LOADING', false, {root: true});
                    commit('SHOW_SUCCESS_SNACKBAR', res.data.message, {root: true});
                    commit('SHOW_DIALOG_UPLOAD_FOTO', false, {root: true});
                    dispatch('getFichas', state.data.current_page);
                }
            })
            .catch(err => {
                if(err) {
                    commit('SET_LOADING', false, {root: true});
                    commit('SHOW_ERROR_SNACKBAR', err.response.data.message, {root: true});
                    dispatch('getFichas', state.data.current_page);
                }
            });
    },

    uploadF2({commit, dispatch, rootState }) {
        const config = {
            headers: { "content-type": "multipart/form-data" },
        };
        let formData = new FormData();
        formData.append("file", rootState.photo);
        formData.append("idinv_ficha_foto", state.id_foto_fi);
        commit('SET_LOADING', true, {root: true});

        axios.post('api/v1/fi2', formData, config)
            .then(res => {
                if(res && res.data) {
                    commit('SET_LOADING', false, {root: true});
                    commit('SHOW_SUCCESS_SNACKBAR', res.data.message, {root: true});
                    commit('SET_ID_FOTO_FI', null );
                    commit('SHOW_DIALOG_UPLOAD_FOTO', false, {root: true});
                    dispatch('getFichas', state.data.current_page);
                }
            })
            .catch(err => {
                if(err && err.response) {
                    commit('SET_LOADING', false, {root: true});
                    commit('SHOW_ERROR_SNACKBAR', err.response.data.message, {root: true});
                    dispatch('getFichas', state.data.current_page);
                }
            });
    },

    deleteF1({ commit, dispatch}, fi) {
        const config = {
            data: { path: fi.path }
        };
        axios.delete('api/v1/fi/'+ fi.idinv_ficha_foto , config)
            .then(res => {
                if(res && res.data) {
                    commit('SHOW_SUCCESS_SNACKBAR', res.data.message, {root: true});
                    dispatch('getFichas', state.data.current_page);
                }
            })
            .catch(err => {
                if(err && err.response) {
                    commit('SHOW_ERROR_SNACKBAR', err.response.data.message, {root: true});
                    dispatch('getFichas', state.data.current_page);
                }
            })
    },

    deleteF2({ commit, dispatch}, fi) {
        const config = {
            data: { path: fi.path2 }
        };
        axios.delete('api/v1/fi2/'+ fi.idinv_ficha_foto, config)
            .then(res => {
                if(res && res.data) {
                    commit('SHOW_SUCCESS_SNACKBAR', res.data.message, {root: true});
                    dispatch('getFichas', state.data.current_page);
                }
            })
            .catch(err => {
                if(err && err.response) {
                    commit('SHOW_ERROR_SNACKBAR', err.response.data.message, {root: true});
                    dispatch('getFichas', state.data.current_page);
                }
            })
    },

    storeMunoz({ commit, dispatch, rootState }, ficha) {
        commit('SET_LOADING', true);
        axios.post('api/v1/munoz', {
            id_paciente: ficha.paciente_isos.idpacientes,
            idpcr_prueba_molecular: ficha.pcr_prueba_molecular.idpcr_pruebas_moleculares,
        })
            .then(res => {
                if (res && res.data) {
                    commit('SHOW_SUCCESS_SNACKBAR', res.data.message, {root: true});
                    commit('SET_LOADING', false);
                    dispatch('getFichas', state.data.current_page);
                }
            })
            .catch(err => {
                if (err && err.response) {
                    commit('SHOW_ERROR_SNACKBAR', err.response.data.message, {root: true});
                    commit('SET_LOADING', false);
                    dispatch('getFichas', state.data.current_page);
                }
            })
    },

    async enviarPM({commit, dispatch}, fichas){
        const ids = fichas.map(ficha =>  ficha.idficha_paciente);
        const data = {ids, id_envio_laboratorio}
        try {
            const res = await axios.post('api/v1/pm/enviar', data)
            if (res && res.data){
                commit('SHOW_SUCCESS_SNACKBAR', await res.data.message, {root: true});
                dispatch('getFichas', state.data.current_page)
            }
        } catch (e) {
            commit('SHOW_ERROR_SNACKBAR', await e.response.data.message, {root: true});
        }
    }
};

const mutations = {
    SET_DATA(state, data) {
        state.data = data;
    },
    SET_ID_FI(state, id) {
        state.id_fi= id;
    },
    SET_ID_FOTO_FI(state, id) {
        state.id_foto_fi= id;
    },
    SET_LOADING(state, loading) {
        state.loading = loading;
    },
    SET_LOADING_TABLE(state, loading) {
        state.loading_table = loading;
    },
    SHOW_DIALOG_BARCODE (state, show) {
        state.dialog.barcode = show;
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

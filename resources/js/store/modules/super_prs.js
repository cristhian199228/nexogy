import axios from 'axios'

const state = {
    data: [],
    loading: false,
    loading_table: false,
    buscar: "",
    dialog: {
        paciente: false,
        mediweb: false,
    },
    pruebas_rapidas: [],
    id_ficha: null,
    filtro: {
        enviado_wp: null,
        empresa: null,
        estacion: null,
        estado: null,
        temperatura: null,
        ps: null,
    },
};
const getters = {};

const actions = {
    async getFichas({commit, rootState }) {
        if (rootState.id_sede) {
            commit('SET_LOADING_TABLE', true);
            const config = {
                params: {
                    buscar: state.buscar,
                    sede: rootState.id_sede,
                    enviado_wp: state.filtro.enviado_wp,
                    empresa: state.filtro.empresa,
                    estacion: state.filtro.estacion,
                    estado: state.filtro.estado,
                    temperatura: state.filtro.temperatura,
                    ps: state.filtro.ps
                }
            }
            try {
                const res = await axios.get('api/v1/super_prs', config)
                commit('SET_DATA', await res.data)
                commit('SET_LOADING_TABLE', false)
            } catch(e) {
                console.error(e.response.data)
                commit('SET_LOADING_TABLE', false);
            }
        }
    },
    async getPrsPasadas( { commit }, id_paciente ) {
        commit("SET_PRUEBAS_RAPIDAS", []);
        try {
            const res = await axios.get('api/v1/ps/'+ id_paciente)
            if (res && res.data) {
                commit('SET_PRUEBAS_RAPIDAS', res.data);
            }
        } catch(e) {
            console.error(e);
        }
    },

    finalizarAtencion({ commit, dispatch }, id_ficha) {
        const snackbar = {};
        axios.put(`api/v1/ficha/${id_ficha}/finish`)
          .then(res => {
              if (res && res.data) {
                  snackbar.show = true;
                  snackbar.color= "success";
                  snackbar.message = res.data.message;
                  commit('SHOW_SNACKBAR', snackbar, {root: true});
                  commit('FINALIZAR_ATENCION', id_ficha);
              }
          })
          .catch(err => {
              if (err && err.response) {
                  snackbar.show = true;
                  snackbar.color= "error";
                  snackbar.message = err.response.data.message;
                  commit('SHOW_SNACKBAR', snackbar, {root: true});
              }
          })
    },
    enviarMw( { commit, dispatch, rootState }, estado_prs) {
        const snackbar = {};
        commit('SET_LOADING', true);
        axios.post('api/v1/cita_mw', {
            idficha_paciente: state.id_ficha,
            estado_prs: estado_prs,
            usuario: rootState.currentUser.user.userName
        })
            .then(res => {
                if (res && res.data) {
                    commit('SET_LOADING', false);
                    commit('SHOW_DIALOG_MEDIWEB', false);
                    commit('SHOW_DIALOG_PACIENTE', false);
                    snackbar.show = true;
                    snackbar.color= "success";
                    snackbar.message = res.data.message;
                    commit('SHOW_SNACKBAR', snackbar, {root: true});
                    dispatch('getFichas');
                }
            })
            .catch(err => {
                if (err && err.response) {
                    commit('SET_LOADING', false);
                    snackbar.show = true;
                    snackbar.color= "error";
                    snackbar.message = err.response.data.message;
                    commit('SHOW_SNACKBAR', snackbar, {root: true});
                    dispatch('getFichas');
                }
            })
    },

    async storeWP({ commit, dispatch, rootState }, prueba) {
        try {
            const res = await axios.post('api/v1/wp', {
                idpruebaserologicas: prueba.idpruebaserologicas,
                usuario: rootState.currentUser.user.userName
            })
            commit('SHOW_SUCCESS_SNACKBAR', await res.data.message, {root: true});
            commit('AGREGAR_MENSAJE_WHATSAPP',  prueba.idfichapacientes);
        } catch (e) {
            commit('SHOW_ERROR_SNACKBAR', await e.response.data.message, {root: true});
        }
    },

    async updateCelular({commit}, data) {
        try {
            const res = await axios.post('api/v1/paciente/celular', data)
            commit('SHOW_SUCCESS_SNACKBAR', await res.data.message, {root: true})
            commit('UPDATE_CELULAR', {
                id_paciente: data.id_paciente,
                celular: await res.data.data
            })
        } catch (e) {
            commit('SHOW_ERROR_SNACKBAR', await e.response.data.message, {root: true});
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
    SET_ID_FICHA(state, id) {
        state.id_ficha = id;
    },
    SET_LOADING_TABLE(state, loading) {
        state.loading_table = loading;
    },
    SHOW_DIALOG_PACIENTE(state, show) {
        state.dialog.paciente = show;
    },
    SHOW_DIALOG_MEDIWEB(state, show) {
        state.dialog.mediweb = show;
    },
    SET_CURRENT_PAGE(state, page) {
        state.data.current_page = page;
    },
    SET_CRITERIO_BUSQUEDA (state, criterio) {
        state.buscar = criterio;
    },
    SET_PRUEBAS_RAPIDAS (state, pruebas) {
        state.pruebas_rapidas = pruebas;
    },
    FINALIZAR_ATENCION(state, id) {
        const ficha = state.data.find(ficha => ficha.idficha_paciente === id);
        ficha.estado = 1;
    },
    AGREGAR_MENSAJE_WHATSAPP(state, id_ficha) {
        const ficha = state.data.find(ficha => ficha.idficha_paciente === id_ficha);
        if (ficha.prueba_serologica.length > 0 && ficha.prueba_serologica[0].resultado) {
            ficha.prueba_serologica[0].envio_w_p_count++;
        }
    },
    SET_FILTRO_MENSAJE_WHATSAPP(state, estado) {
        state.filtro.enviado_wp = estado
    },
    SET_FILTRO_EMPRESA(state, estado) {
        state.filtro.empresa = estado
    },
    SET_FILTRO_ESTACION(state, estado) {
        state.filtro.estacion = estado
    },
    SET_FILTRO_ESTADO(state, estado) {
        state.filtro.estado = estado
    },
    SET_FILTRO_TEMPERATURA(state, estado) {
        state.filtro.temperatura = estado
    },
    SET_FILTRO_PS(state, estado) {
        state.filtro.ps = estado
    },
    UPDATE_CELULAR (state, { id_paciente, celular }) {
        const [paciente] = state.data.map(ficha => ficha.paciente_isos).filter(paciente => paciente.idpacientes === id_paciente)
        paciente.celular = celular
    }
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}

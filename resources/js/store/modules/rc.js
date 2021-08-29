import axios from "axios";
import router from "../../router";
import moment from "moment";

const state = {
    data: {},
    loading: false,
    loading_table: false,
    buscar: "",
    dialog: {
        nueva_evidencia: false,
        nuevo_contacto: false,
        pdf: false
    },
    id_ev: null,
    id_fe: null,
    id_fc: null,
    contactos: [],
    filtros: {
        estacion: null,
        sede: null,
        empresa: null,
        fechas: [
            moment().format('YYYY-MM-DD'),
            moment().format('YYYY-MM-DD')
        ],
    },
    indicaciones_medicas: null
};

const getters = {
    getEvidenciaById(state) {
        return state.data.data.find(ev => ev.id === state.id_ev);
    },
    currentPage: state => state.data.current_page,
    getBuscar: state => state.buscar
};

const actions = {
    async getFichas({ commit, dispatch, rootGetters, getters }, page) {
        commit("SET_LOADING_TABLE", true);
        const config = {
            params: {
                page,
                buscar: getters.getBuscar,
                estacion: state.filtros.estacion,
                sede: state.filtros.sede,
                empresa: state.filtros.empresa,
                fecha_inicio: state.filtros.fechas[0],
                fecha_final: state.filtros.fechas[1],
            }
        };
        try {
            const res = await axios.get(`api/v1/rc`, config);
            await commit("SET_LOADING_TABLE", false);
            await commit("SET_DATA", res.data);
        } catch (e) {
            commit("SET_LOADING_TABLE", false);
        }
    },
    async store({ commit, dispatch, getters, rootGetters }, params) {
        commit("SET_LOADING", true);
        try {
            const res = await axios.post("api/v1/evidencia", {
                id_paciente: params.id_paciente,
                id_empresa: params.id_empresa,
                id_estacion: params.id_estacion,
                celular: params.celular,
                correo: params.correo,
                usuario: rootGetters["currentUser/getUserEmail"]
            });
            commit("SET_LOADING", false);
            commit("SHOW_SUCCESS_SNACKBAR", await res.data.message, {
                root: true
            });
            commit("SHOW_DIALOG_EVIDENCIA", false);
            return dispatch("getFichas", getters.currentPage);
        } catch (e) {
            commit("SET_LOADING", false);
            commit("SHOW_ERROR_SNACKBAR", await e.response.data.message, {
                root: true
            });
        }
    },
    async habilitarFotos({ commit, dispatch }, id_ev) {
        try {
            const res = await axios.put(
                `api/v1/evidencia/habilitarFotos/${id_ev}`
            );
            if (res && res.data) {
                commit("SHOW_SUCCESS_SNACKBAR", res.data.message, {root: true});
                dispatch("getFichas", state.data.current_page);
            }
        } catch (e) {
            commit("SHOW_ERROR_SNACKBAR", await e.response.data.message, {root: true});
        }
    },
    async update({ commit, dispatch }, id_ev) {
        try {
            const res = await axios.put(`api/v1/evidencia/${id_ev}`);
            if (res && res.data) {
                commit("SHOW_SUCCESS_SNACKBAR", res.data.message, {root: true});
                dispatch("getFichas", state.data.current_page);
            }
        } catch (e) {
            commit("SHOW_ERROR_SNACKBAR", await e.response.data.message, {root: true});
        }
    },
    async delete({ commit, dispatch }, id_evidencia) {
        try {
            const res = await axios.delete(`api/v1/evidencia/${id_evidencia}`);
            commit("SHOW_SUCCESS_SNACKBAR", await res.data.message, {root: true});
            commit("SHOW_DIALOG_EVIDENCIA", false);
            return dispatch("getFichas", state.data.current_page);
        } catch (e) {
            commit("SHOW_ERROR_SNACKBAR", await e.response.data.message, {root: true});
        }
    },
    async storeFe({ commit, dispatch, rootState }, id_evidencia) {
        try {
            const res = await axios.post("api/v1/fe", {id_evidencia});
            commit("SHOW_SUCCESS_SNACKBAR", await res.data.message, {root: true});
            dispatch("getFichas", state.data.current_page);
        } catch (e) {
            commit("SHOW_ERROR_SNACKBAR", await e.response.data.message, {root: true});
        }
    },
    async updateFe({ commit, dispatch }, { pac, fe, p1 }) {
        try {
            const res = await axios.put(`api/v1/fe/${fe.id}`, {
                id_paciente: pac.idpacientes,
                direccion: pac.direccion,
                celular: pac.celular,
                nro_registro: pac.nro_registro,
                nombre_supervisor: fe.nombre_supervisor,
                celular_supervisor: fe.celular_supervisor,
                p1_fecha_inicio: p1[0],
                p1_fecha_fin: p1[1],
                prueba_positiva: fe.prueba_positiva,
                prueba_cv: fe.prueba_cv,
                prueba_otro: fe.prueba_otro,
                prueba_otro_tipo: fe.prueba_otro_tipo,
                prueba_otro_fecha: fe.prueba_otro_fecha,
                prueba_otro_resultado: fe.prueba_otro_resultado,
                prueba_otro_lugar: fe.prueba_otro_lugar,
                observaciones: fe.observaciones
            });
            commit("SHOW_SUCCESS_SNACKBAR", await res.data.message, {root: true});
            commit("SET_ID_EV", null);
            await router.push("/rc");
        } catch (e) {
            commit("SHOW_ERROR_SNACKBAR", await e.response.data.message, {root: true});
        }
    },

    async deleteFe({ commit, dispatch }, id_fe) {
        try {
            const res = await axios.delete(`api/v1/fe/${id_fe}`);
            commit("SHOW_SUCCESS_SNACKBAR", await res.data.message, {root: true});
            dispatch("getFichas", state.data.current_page);
        } catch (e) {
            commit("SHOW_ERROR_SNACKBAR", await e.response.data.message, {root: true});
        }
    },

    async storeFc({ commit, dispatch, rootState }, id_evidencia) {
        try {
            const res = await axios.post("api/v1/fc", {id_evidencia});
            commit("SHOW_SUCCESS_SNACKBAR", await res.data.message, {root: true});
            dispatch("getFichas", state.data.current_page);
        } catch (e) {
            commit("SHOW_ERROR_SNACKBAR", await e.response.data.message, {root: true});
        }
    },

    async updateFc({ commit, dispatch }, fc) {
        try {
            const res = await axios.put(`api/v1/fc/${fc.id}`, {
                estado: fc.estado
            });
            commit("SHOW_SUCCESS_SNACKBAR", await res.data.message, {root: true});
            commit("SET_ID_EV", null);
            await router.push("/rc");
        } catch (e) {
            commit("SHOW_ERROR_SNACKBAR", await e.response.data.message, {root: true});
        }
    },

    async deleteFc({ commit, dispatch }, id_fc) {
        try {
            const res = await axios.delete(`api/v1/fc/${id_fc}`);
            commit("SHOW_SUCCESS_SNACKBAR", await res.data.message, {root: true});
            dispatch("getFichas", state.data.current_page);
        } catch (e) {
            commit("SHOW_ERROR_SNACKBAR", await e.response.data.message, {root: true});
        }
    },

    async storeCD({ commit, dispatch }, params) {
        try {
            const res = await axios.post("api/v1/cd", {
                id_fe: state.id_fe,
                nombres: params.nombre_completo,
                cargo: params.cargo,
                celular: params.celular,
                detalle: params.detalle
            });
            commit("SHOW_SUCCESS_SNACKBAR", await res.data.message, {root: true});
            commit("SHOW_DIALOG_CONTACTO", false);
            return dispatch("getContactos");
        } catch (e) {
            commit("SHOW_ERROR_SNACKBAR", await e.response.data.message, {root: true});
        }
    },

    uploadFEV({ commit, dispatch, rootState }) {
        const config = {
            headers: { "content-type": "multipart/form-data" }
        };
        const formData = new FormData();
        formData.append("file", rootState.photo);
        formData.append("id_evidencia", state.id_ev);
        commit("SET_LOADING", true, { root: true });

        axios.post("api/v1/fev", formData, config)
            .then(res => {
                commit("SET_LOADING", false, { root: true });
                commit("SHOW_DIALOG_UPLOAD_FOTO", false, { root: true });
                commit("SHOW_SUCCESS_SNACKBAR", res.data.message, {root: true});
                dispatch("getFichas", state.data.current_page);
            })
            .catch(err => {
                commit("SET_LOADING", false, { root: true });
                commit("SHOW_ERROR_SNACKBAR", err.response.data.message, {root: true});
                dispatch("getFichas", state.data.current_page);
            });
    },

    async getContactos({ commit }) {
        commit("SET_CONTACTOS", []);
        try {
            const res = await axios.get(`api/v1/cd/${state.id_fe}`);
            commit("SET_CONTACTOS", await res.data);
        } catch (e) {
            console.error(e);
        }
    },

    async storeIndicacionesMedicas({ commit, dispatch, getters }, id_evidencia) {
        try {
            const res = await axios.post("api/v1/im", {id_evidencia});
            commit("SHOW_SUCCESS_SNACKBAR", await res.data.message, {
                root: true
            });
            await router.push({
                name: "EditarIm",
                params: { idEvidencia: id_evidencia }
            });
            //dispatch('getFichas', getters.currentPage)
        } catch (e) {
            commit("SHOW_ERROR_SNACKBAR", e.response.data.message, {
                root: true
            });
        }
    },

    async updateIndicacionesMedicas({ commit }, data) {
        try {
            const res = await axios.put(`api/v1/im/${data.id}`, data);
            commit("SHOW_SUCCESS_SNACKBAR", await res.data.message, {
                root: true
            });
            await router.push("/rc");
        } catch (e) {
            commit("SHOW_ERROR_SNACKBAR", e.response.data.message, {
                root: true
            });
        }
    },

    async deleteIndicacionesMedicas({ commit, dispatch, getters }, id) {
        try {
            const res = await axios.delete(`api/v1/im/${id}`);
            commit("SHOW_SUCCESS_SNACKBAR", await res.data.message, {
                root: true
            });
            dispatch("getFichas", getters.currentPage);
        } catch (e) {
            commit("SHOW_ERROR_SNACKBAR", e.response.data.message, {
                root: true
            });
        }
    },

    async showIndicacionesMedicas({ commit }, id) {
        commit("SET_INDICACIONES_MEDICAS", null);
        try {
            const res = await axios.get(`api/v1/im/${id}`);
            commit("SET_INDICACIONES_MEDICAS", await res.data);
        } catch (e) {
            //commit('SHOW_ERROR_SNACKBAR', e.response.data.message, {root: true});
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
    SET_CRITERIO_BUSQUEDA(state, criterio) {
        state.buscar = criterio;
    },
    SHOW_DIALOG_EVIDENCIA(state, show) {
        state.dialog.nueva_evidencia = show;
    },
    SHOW_DIALOG_PDF(state, show) {
        state.dialog.pdf = show;
    },
    SHOW_DIALOG_CONTACTO(state, show) {
        state.dialog.nuevo_contacto = show;
    },
    SET_FILTRO_ESTACION(state, estacion) {
        state.filtros.estacion = estacion;
    },
    SET_FILTRO_SEDE(state, sede) {
        state.filtros.sede = sede;
    },
    SET_FILTRO_EMPRESA(state, empresa) {
        state.filtros.empresa = empresa;
    },
    SET_FILTRO_FECHA (state, fechas){
        state.filtros.fechas = fechas
    },
    SET_ID_EV(state, id) {
        state.id_ev = id;
    },
    SET_ID_FE(state, id) {
        state.id_fe = id;
    },
    SET_ID_FC(state, id) {
        state.id_fc = id;
    },
    SET_CONTACTOS(state, data) {
        state.contactos = data;
    },
    SET_INDICACIONES_MEDICAS(state, data) {
        state.indicaciones_medicas = data;
    }
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};

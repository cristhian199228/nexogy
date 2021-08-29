import axios from 'axios'
import moment from 'moment'

const state = {
    data: {},
    loading: false,
    loading_table: false,
    dialog: {
        edit: false,
        foto: false,
    },
    tipos: [],
    filtros: {
        fechas: [
            moment().format('YYYY-MM-DD'),
            moment().format('YYYY-MM-DD')
        ],
        resultado: null,
        empresa: null,
        sede: null,
        buscar: null,
        tipo: null,
    }
};
const getters = {
    getCurrentPage: state => state.data.current_page,
};

const actions = {
    async getFichas({commit, dispatch }, page) {
        commit('SET_LOADING_TABLE', true);
        const config = {
            params: {
                page,
                buscar: state.filtros.buscar,
                fecha_inicio: state.filtros.fechas[0],
                fecha_final: state.filtros.fechas[1],
                resultado: state.filtros.resultado,
                empresa: state.filtros.empresa,
                sede: state.filtros.sede,
                tipo: state.filtros.tipo
            }
        };
        try {
            const res = await axios.get('api/v1/admin_pcr', config)
            commit('SET_DATA', await res.data);
            commit('SET_LOADING_TABLE', false);
            dispatch('getTiposPcr');
        } catch(e) {
            commit('SET_LOADING_TABLE', false);
        }
    },
    async getTiposPcr({commit }) {
        if(state.tipos.length === 0) {
            try {
                const res = await axios.get('api/v1/pm/tipos')
                if (res && res.data) {
                    commit('SET_TIPOS', res.data)
                }
            } catch(e) {
                console.error(e);
            }
        }
    },

    deletePM({ commit, dispatch}, id_pm) {
        const snackbar = {};
        axios.delete('api/v1/pm/'+ id_pm)
          .then(res => {
              if(res && res.data) {
                  snackbar.show = true;
                  snackbar.color= "success";
                  snackbar.message = "Se eliminó la prueba molecular correctamente";
                  commit('SHOW_SNACKBAR', snackbar, {root: true});
                  dispatch('getFichas', state.data.current_page);
              }
          })
          .catch(err => {
              if(err && err.response) {
                  snackbar.show = true;
                  snackbar.color= "error";
                  snackbar.message = err.response.data.message;
              }
          })
    },

    updatePM({ commit, dispatch}, params) {
        const snackbar = {};
        commit('SET_LOADING', true, {root: true});
        axios.put('api/v1/pm/'+ params.id_pm , {
            precio: params.precio,
            tipo: params.tipo,
            resultado: params.resultado,
            detalle: params.detalle,
            enviar_mensaje: params.enviar_mensaje,
        })
          .then(res => {
              if(res && res.data) {
                  commit('SET_LOADING', false, {root: true});
                  snackbar.show = true;
                  snackbar.color= "success";
                  snackbar.message = res.data.message;
                  commit('SHOW_SNACKBAR', snackbar, {root: true});
                  commit('SHOW_DIALOG_EDIT_PCR', false);
                  dispatch('getFichas', state.data.current_page);
              }
          })
          .catch(err => {
              if(err && err.response) {
                  commit('SET_LOADING', false, {root: true});
                  snackbar.show = true;
                  snackbar.color= "error";
                  snackbar.message = err.response.data.message;
              }
          })
    },
    uploadPhoto({commit}, params) {
        const config = {
            headers: { "content-type": "multipart/form-data" },
        };
        const formData = new FormData();
        formData.append("file", params.photo);
        formData.append("idpcr_prueba_molecular", params.id_pcr);
        formData.append("detalle", params.detalle);

        return axios.post('api/v1/fotoPcr', formData, config);
    },
    deletePhoto({commit, dispatch}, id_foto_muestra){
        const snackbar = {};
        axios.delete(`api/v1/fotoPcr/${id_foto_muestra}`)
          .then(res => {
              if (res && res.data) {
                  snackbar.show = true;
                  snackbar.color= "success";
                  snackbar.message = "Se eliminó correctamente";
                  commit('SHOW_SNACKBAR', snackbar, {root: true});
                  dispatch('getFichas', state.data.current_page);
              }
          })
          .catch(err => {
              if (err && err.response) {
                  snackbar.show = true;
                  snackbar.color= "error";
                  snackbar.message = "Ocurrió un error al eliminar";
                  commit('SHOW_SNACKBAR', snackbar, {root: true});
              }
          })
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
        state.filtros.buscar = criterio;
    },
    SET_TIPOS (state, tipos) {
        state.tipos = tipos;
    },
    SHOW_DIALOG_EDIT_PCR (state, show) {
        state.dialog.edit = show;
    },
    SHOW_DIALOG_UPLOAD_FOTO_MUESTRA (state, show) {
        state.dialog.foto = show;
    },
    SET_FILTRO_FECHA (state, fechas){
        state.filtros.fechas = fechas
    },
    SET_FILTRO_RESULTADO(state, resultado){
        state.filtros.resultado = resultado;
    },
    SET_FILTRO_EMPRESA(state, empresa){
        state.filtros.empresa = empresa;
    },
    SET_FILTRO_SEDE(state, sede){
        state.filtros.sede = sede;
    },
    SET_FILTRO_TIPO(state, tipo){
        state.filtros.tipo = tipo;
    }
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}

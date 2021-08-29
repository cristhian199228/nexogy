import Vue from 'vue'
import Vuex from 'vuex'
import currentUser from './modules/currentUser'
import admision from "./modules/admision";
import controlador from "./modules/controlador";
import pcr from "./modules/pcr";
import super_pcr from "./modules/super_pcr";
import salud from "./modules/salud";
import super_prs from "./modules/super_prs";
import admin_pcr from "./modules/admin_pcr";
import envio_pcr from "./modules/envio_pcr";
import pacientes from "./modules/pacientes";
import rc from "./modules/rc";
import is from "./modules/is"
import pa from "./modules/pa"

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        currentUser,
        admision,
        controlador,
        pcr,
        super_pcr,
        super_prs,
        salud,
        admin_pcr,
        rc,
        envio_pcr,
        pacientes,
        is,
        pa
    },
    state: {
        snackbar: {
            show: false,
            message: "",
            color: "",
        },
        dialog: {
            estacion: false,
            sede: false,
            foto: false,
            reporte: false
        },
        estaciones: [],
        sedes: [],
        empresas: [],
        departamentos: [],
        provincias: [],
        distritos: [],
        id_estacion: null,
        id_sede: null,
        loading: false,
        photo: undefined,
        search: true,
        resultados_prs: ['', 'NEG', 'IGG R', 'IGG V', 'IGG', 'IGM', 'IGM/IGG', 'IGM P', 'IGM/IGG P', 'INVALIDO'],
        colores_prs: ['', 'green', 'orange', 'orange', 'red', 'red', 'red', 'red', 'red', 'yellow'],
        resultados_pcr: ['NEG', 'POS', 'ANULADO'],
        colores_pcr: ['green', 'red', 'orange'],
    },
    getters: {
        getEstacionById: (state) => (id) => {
            return state.estaciones.find(estacion => estacion.idestaciones === id);
        },
        estacionesEvidencias: state => {
            return state.estaciones.filter(estacion => {
                return estacion.idestaciones === 1 || estacion.idestaciones === 7 || estacion.idestaciones === 11 ||
                  estacion.idestaciones === 24 || estacion.idestaciones === 7 || estacion.idsede === 4 || estacion.idestaciones === 25
                || estacion.idestaciones === 38 || estacion.idestaciones === 26
            })
        },
        nombreEstacion(state, getters) {
            const estacion = getters.getEstacionById(state.id_estacion);
            let nom_estacion = "";
            if (estacion) nom_estacion = estacion.nom_estacion;
            return nom_estacion;
        },
        getSedeById: (state) => (id) => {
            return state.sedes.find(sede => sede.idsedes === id);
        },
        nombreSede(state, getters) {
            const sede = getters.getSedeById(state.id_sede);
            let nom_sede = "";
            if (sede) nom_sede = sede.descripcion;
            return nom_sede;
        },
        getStrTipoDocumento: () => (tipo_doc) => {
            switch (tipo_doc) {
                case 1: return "DNI";
                case 2: return "CE";
                case 3: return "PASAPORTE";
                case 7: return "RUT CHILENO";
                default: return "";
            }
        },
        getPrsStrResult: (state) => (result) => state.resultados_prs[result],
        getPrsColorResult: (state) => (result) => `${state.colores_prs[result]} darken-2`,
        getPcrStrResult: (state) => (result) => state.resultados_pcr[result],
        getPcrColorResult: (state) => (result) => `${state.colores_pcr[result]} darken-2`,
        getEstacionesPorSede: state => state.estaciones.filter(e => e.idsede === state.id_sede),
        getSede: state => state.id_sede,
        getSedes: state => state.sedes,
        getEstacion :state => state.id_estacion,
    },
    actions: {
        async getEstaciones({ commit, rootState }) {
            if (!rootState.id_estacion) {
                try {
                    const res = await axios.get('/api/v1/estacion');
                    if (res && res.data) {
                        commit('SET_ESTACIONES', await res.data.estaciones);
                    }
                } catch (e) {
                    console.error(e.response)
                }
            }
        },

        async getSedes({ commit, rootState }) {
            if (!rootState.id_sede) {
                try {
                    const res = await axios.get('/api/v1/sede');
                    if (res && res.data) {
                        commit('SET_SEDES', res.data.sedes);
                    }
                } catch (e) {
                    //console.log(e);
                }
            }
        },
        async getEmpresas({commit}, buscar) {
            const config = {
                params: { buscar }
            }
            try {
                const res = await axios.get('/api/v1/searchEmpresa', config)
                commit('SET_EMPRESAS', await res.data)
            } catch (e) {
                console.error(e.response);
            }
        },
        async getDepartamentos({commit}, buscar) {
            const config = {
                params: { buscar }
            }
            try {
                const res = await axios.get('/api/v1/searchDepartamento', config)
                commit('SET_DEPARTAMENTOS', await res.data.departamentos)
            } catch (e) {
                console.error(e.response);
            }
        },
        async getProvincias({commit}, buscar) {
            const config = {
                params: { buscar }
            }
            try {
                const res = await axios.get('/api/v1/searchProvincia', config)
                commit('SET_PROVINCIAS', await res.data.provincias)
            } catch (e) {
                console.error(e.response);
            }
        },
        async getDistritos({commit}, buscar) {
            const config = {
                params: { buscar }
            }
            try {
                const res = await axios.get('/api/v1/searchDistrito', config)
                commit('SET_DISTRITOS', await res.data.distritos)
            } catch (e) {
                console.error(e.response);
            }
        },
    },
    mutations: {
        SHOW_SNACKBAR(state, snackbar) {
            state.snackbar.show = snackbar.show;
            state.snackbar.message = snackbar.message;
            state.snackbar.color = snackbar.color;
        },
        SHOW_DIALOG_ESTACION (state, show) {
            state.dialog.estacion = show;
        },
        SHOW_DIALOG_SEDE (state, show) {
            state.dialog.sede = show;
        },
        SET_PHOTO(state, photo) {
            state.photo = photo;
        },
        SHOW_SEARCH(state, show) {
            state.search = show;
        },
        SET_LOADING(state, loading) {
            state.loading = loading;
        },
        SHOW_DIALOG_UPLOAD_FOTO (state, show) {
            state.dialog.foto = show;
        },
        SHOW_DIALOG_REPORTE (state, show) {
            state.dialog.reporte = show;
        },
        SET_ESTACIONES(state, estaciones) {
            state.estaciones = estaciones;
        },
        SET_SEDES(state, sedes) {
            state.sedes = sedes;
        },
        SET_ID_ESTACION (state, id_estacion) {
            state.id_estacion = id_estacion;
        },
        SET_ID_SEDE (state, id) {
            state.id_sede = id;
        },
        SHOW_ERROR_SNACKBAR(state, message){
            state.snackbar.show = true;
            state.snackbar.color = 'error';
            state.snackbar.message = message;
        },
        SHOW_SUCCESS_SNACKBAR(state, message){
            state.snackbar.show = true;
            state.snackbar.color = 'success';
            state.snackbar.message = message;
        },
        SET_EMPRESAS(state, data) {
            state.empresas = data;
        },
        SET_DEPARTAMENTOS(state, data) {
            state.departamentos = data;
        },
        SET_PROVINCIAS(state, data) {
            state.provincias = data;
        },
        SET_DISTRITOS(state, data) {
            state.distritos = data;
        },
    },
})

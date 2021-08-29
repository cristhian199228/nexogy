<template>
  <div>
    <viewer :images="images" @inited="inited" class="viewer" ref="viewer">
      <img v-for="(src,i) in images" :src="src" :key="i" class="image" />
    </viewer>
    <buscador :store="modulo" title="ADMISION" />
    <v-data-table :headers="headers" :items="fichas" :loading="loading_table" :items-per-page="15" hide-default-footer>
      <template v-slot:item.contador="{item}">
        <small>{{ item.contador }}</small>
      </template>
      <template v-slot:item.nom_completo="{item}">
        <small v-if="item.paciente_isos">{{ item.paciente_isos.nom_completo }}</small>
        <v-btn icon small v-if="item.turno !== 3" @click="cambiarTurno(item)">
          <v-icon small v-if="item.turno === 1">mdi-arrow-up-bold</v-icon>
          <v-icon small v-else-if="item.turno === 2">mdi-arrow-down-bold</v-icon>
        </v-btn>
      </template>
      <template v-slot:item.nom_empresa="{item}">
        <small v-if="item.paciente_isos && item.paciente_isos.empresa">
          {{ item.paciente_isos.empresa.descripcion }}
        </small>
      </template>
      <template v-slot:item.ct="{ item }">
        <template v-if="item.declaracion_jurada.length > 0">
          <v-btn small icon @click="verFoto('/api/v1/dj/' + item.declaracion_jurada[0].path)"><v-icon small>mdi-eye</v-icon></v-btn>
          <v-btn small icon @click="accionesDeleteCT(item.declaracion_jurada[0])"><v-icon small>mdi-delete</v-icon></v-btn>
        </template>
        <template v-else>
          <v-btn small icon @click="accionesUploadCT(item.idficha_paciente)"><v-icon small>mdi-camera</v-icon></v-btn>
        </template>
      </template>
      <template v-slot:item.ci="{ item }">
        <template v-if="item.consentimiento_informado.length > 0" >
          <v-btn small icon @click="verFoto('/api/v1/ci/' + item.consentimiento_informado[0].path)"><v-icon small>mdi-eye</v-icon></v-btn>
          <v-btn small icon @click="accionesDeleteCI(item.consentimiento_informado[0])"><v-icon small>mdi-delete</v-icon></v-btn>
        </template>
        <template v-else>
          <v-btn small icon @click="accionesUploadCI(item.idficha_paciente)"><v-icon small>mdi-camera</v-icon></v-btn>
        </template>
      </template>
      <template v-slot:item.dc="{ item }">
        <template v-if="item.datos_clinicos.length > 0">
          <v-chip dark small color="red darken-2">SI</v-chip>
          <v-btn small icon @click="accionesEditDC(item)"><v-icon small>mdi-pencil</v-icon></v-btn>
          <v-btn small icon @click="accionesDeleteDC(item.datos_clinicos[0].iddatoclinicos)"><v-icon small>mdi-delete</v-icon></v-btn>
        </template>
        <template v-else>
          <v-chip dark small color="green darken-2">NO</v-chip>
          <v-btn small icon @click="accionesStoreDC(item.idficha_paciente)"><v-icon small>mdi-plus-circle-outline</v-icon></v-btn>
        </template>
      </template>
      <template v-slot:item.ae="{ item }">
        <template v-if="item.antecedentes_ep.length > 0">
          <v-chip dark small color="red darken-2">SI</v-chip>
          <v-btn small icon @click="accionesEditAE(item)"><v-icon small>mdi-pencil</v-icon></v-btn>
          <v-btn small icon @click="accionesDeleteAE(item.antecedentes_ep[0].idaepidemologicos)"><v-icon small>mdi-delete</v-icon></v-btn>
        </template>
        <template v-else>
          <v-chip dark small color="green darken-2">NO</v-chip>
          <v-btn small icon @click="accionesStoreAE(item.idficha_paciente)"><v-icon small>mdi-plus-circle-outline</v-icon></v-btn>
        </template>
      </template>
      <template v-slot:item.temp="{ item }">
        <template v-if="item.temperatura.length > 0">
          <v-chip dark small  :color="item.temperatura[0].valortemp >= 37.8 ? 'red darken-2' : 'green darken-2'">
            {{item.temperatura[0].valortemp}}
          </v-chip>
          <v-btn small icon @click="accionesDeleteTemp(item.temperatura[0].idtemperatura)"><v-icon small>mdi-delete</v-icon></v-btn>
        </template>
        <v-btn small v-else icon @click="accionesStoreTemp(item.idficha_paciente)">
          <v-icon small>mdi-thermometer-plus</v-icon>
        </v-btn>
      </template>
    </v-data-table>
    <v-btn class="mr-15" @click="firmar" color="pink" fixed bottom right fab dark><v-icon>mdi-plus</v-icon></v-btn>
    <update-fab-button :store="modulo" />
    <paginate :store="modulo" />
    <DialogStoreTemp />
    <DialogUploadPhoto :store="modulo" :resource="recurso" :title="title" />
    <DialogSaveDC v-show="dialog2.dc" :dc="dc" @close-dialog="dialog2.dc = false" :action="accion" :show-dialog="dialog2.dc" />
    <DialogSaveAE v-show="dialog2.ae" :ae="ae" @close-dialog="dialog2.ae = false" :action="accion" :show-dialog="dialog2.ae" />
  </div>
</template>

<script>
import Viewer from "v-viewer/src/component.vue";
import DialogStoreTemp from "./DialogStoreTemp";
import DialogUploadPhoto from "../DialogUploadPhoto";
import DialogSaveDC from "./DialogSaveDC";
import DialogSaveAE from "./DialogSaveAE";
import paginate from "../paginate";
import UpdateFabButton from "../UpdateFabButton";
import buscador from "../buscador";

import { mapState } from 'vuex';

export default {
  components: {
    DialogSaveDC,
    DialogSaveAE,
    Viewer,
    DialogStoreTemp,
    DialogUploadPhoto,
    paginate,
    UpdateFabButton,
    buscador
  },
  data() {
    return{
      modulo: "admision",
      recurso: "",
      title: "",
      headers: [
        { text: 'N°', align: 'start', value: 'contador' },
        { text: 'Nombres', value: 'nom_completo', sortable: false },
        { text: 'Empresa', value: 'nom_empresa'},
        { text: 'CT', value: 'ct', sortable: false },
        { text: 'CI', value: 'ci', sortable: false },
        { text: 'DC', value: 'dc', sortable: false },
        { text: 'AE', value: 'ae', sortable: false },
        { text: 'T°', value: 'temp', sortable: false },
      ],
      images: [],
      dc: {
        iddatoclinicos: null,
        idfichapacientes: null,
        tos: false,
        dolor_garganta: false,
        congestion_nasal: false,
        dificultad_respiratoria: false,
        fiebre: false,
        malestar_general: false,
        diarrea: false,
        nauseas_vomitos: false,
        cefalea: false,
        irritabilidad_confusion: false,
        falta_aliento: false,
        anosmia_ausegia: false,
        otros: null,
        toma_medicamento: null,
        fecha_inicio_sintomas: null,
        post_vacunado: false,
      },
      ae: {
        idfichapacientes: null,
        dias_viaje: false,
        contacto_cercano: false,
        conv_covid: false,
        paises_visitados: null,
        debilite_sistema: null,
        medio_transporte: null,
        fecha_llegada: null,
        fecha_ultimo_contacto: null,
      },
      dialog2: {
        dc: false,
        ae: false,
      },
      accion: null,
    }
  },
  computed: {
    ...mapState('admision', ['dialog','loading_table']),
    fichas: {
      get() {
        return this.$store.state.admision.data.data;
      }
    }
  },
  methods: {
    accionesUploadCT(id_ficha) {
      this.resetChildrenRefs();
      this.$store.commit('SET_PHOTO', undefined);
      this.$store.commit('admision/SET_ID_FICHA', id_ficha);
      this.recurso = "CT";
      this.title = "CUESTIONARIO DE TAMIZAJE";
      this.$store.commit('SHOW_DIALOG_UPLOAD_FOTO', true);
    },
    accionesUploadCI(id_ficha) {
      this.resetChildrenRefs();
      this.$store.commit('SET_PHOTO', undefined);
      this.$store.commit('admision/SET_ID_FICHA', id_ficha);
      this.recurso = "CI";
      this.title = "CONSENTIMIENTO INFORMADO";
      this.$store.commit('SHOW_DIALOG_UPLOAD_FOTO', true);
    },
    accionesDeleteCT(dj) {
      let r = confirm("Está seguro de borrar la foto?");
      if (r) this.$store.dispatch('admision/deleteCT', dj);
    },
    accionesDeleteCI(ci) {
      let r = confirm("Está seguro de borrar la foto?");
      if (r) this.$store.dispatch('admision/deleteCI', ci);
    },
    accionesStoreDC(id_ficha) {
      this.accion = "store"
      this.$store.commit('admision/SET_ID_FICHA', id_ficha);
      this.dialog2.dc = true
    },
    accionesEditDC(ficha) {
      this.accion = "update"
      this.dc = Object.assign({}, ficha.datos_clinicos[0])
      this.dialog2.dc = true
    },
    accionesDeleteDC(id_dc) {
      let r = confirm("Esta seguro de borrar los datos clínicos?");
      if (r) this.$store.dispatch('admision/deleteDC', id_dc);
    },
    accionesStoreAE(id_ficha) {
      this.accion = "store"
      this.$store.commit('admision/SET_ID_FICHA', id_ficha);
      this.dialog2.ae = true
      //this.resetAE();
    },
    accionesEditAE(ficha) {
      this.accion = "update"
      this.ae = Object.assign({}, ficha.antecedentes_ep[0])
      this.dialog2.ae = true
    },
    accionesDeleteAE(id_ae) {
      let r = confirm("Esta seguro de borrar los antecedentes?");
      if (r) this.$store.dispatch('admision/deleteAE', id_ae);
    },
    accionesStoreTemp(id_ficha) {
      this.$store.commit('admision/SET_ID_FICHA', id_ficha);
      this.$store.commit('admision/SHOW_DIALOG_STORE_TEMP', true);
    },
    accionesDeleteTemp(id_temp) {
      let r = confirm("Esta seguro de borrar la temperatura?");
      if (r) this.$store.dispatch('admision/deleteTemp', id_temp);
    },
    cambiarTurno(ficha) {
      let r = confirm("Esta seguro de cambiar el turno del paciente?");
      if (r) this.$store.dispatch('admision/updateTurno', ficha);
    },
    verFoto(ruta){
      this.images = [];
      this.images.push(ruta);
      this.$viewer.show();
    },
    inited (viewer) {
      this.$viewer = viewer
    },
    show () {
      this.$viewer.show()
    },
    resetChildrenRefs() {
      if (this.$children && this.$children.length > 0) {
        for (let i = 0; i < this.$children.length; i++ ) {
          if (this.$children[i].$refs && this.$children[i].$refs.photo) {
            this.$children[i].$refs.photo.value = null;
          }
        }
      }
    },
    resetDC() {
      const dc = this.$children[7].form;
      for (const key of Object.keys(dc)) {
        dc[key] = false;
        if (key === 'toma_med_texto' || key === 'otros_texto') dc[key] = '';
      }
    },
    resetAE() {
      const ae = this.$children[10].form;
      for (const key of Object.keys(ae)) {
        ae[key] = false;
        if (key === 'fecha_contacto' || key === 'fecha_llegada') ae[key] = null;
        if (key === 'condicion_existente_texto' || key === 'medio_transporte' || key === 'paises_visitados') {
          ae[key] = '';
        }
      }
    },
    async firmar() {
      await this.$router.push({name: 'nueva_ficha'})
      /*try {
        const res = await this.$msal.msGraph('/me?$select=employeeId')
        const dni = res.body.employeeId
        if (dni) {
          const resFirma = await axios.get(`api/v1/firma/${dni}`)
          if (resFirma.data) {
            await this.$router.push({name: 'nueva_ficha'})
          } else {
            await this.$router.push({
              name: 'firma',
              params: {
                numero_documento: dni
              }
            })
          }
        } else {
          let enteredDni = prompt('Ingrese dni')
          let resDni = await axios.post('api/v1/searchByDni', {numero_documento: enteredDni})
          if (resDni.data) {
            try {
              console.log(await this.$msal.acquireToken({scopes: ["user.readwrite"]}))
              await this.$msal.msGraph({
                url: "/me",
                method: "PATCH",
                data: {
                  employeeId : enteredDni,
                },
                headers: {
                  contentType: "application/json"
                },
              })
              this.$store.commit('SHOW_SUCCESS_SNACKBAR', 'DNI GUARDADO CORRECTAMENTE')
            } catch (e) {
              console.error(e)
            }
          } else {
            this.$store.commit('SHOW_ERROR_SNACKBAR', 'Numero de documento no válido')
          }
        }
      } catch (e) {
        console.error(e.response.data)
      }*/
    }
  },
  created() {
    this.$store.dispatch('admision/getFichas', 1);
  }
}
</script>

<style>
.image{
  display: none;
}
</style>

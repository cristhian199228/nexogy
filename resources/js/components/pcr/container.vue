<template>
  <div>
    <viewer :images="images" @inited="inited" class="viewer" ref="viewer">
      <img v-for="(src,i) in images" :src="src" :key="i" class="image" />
    </viewer>
    <v-card outlined>
      <buscador :store="modulo" title="CONTROLADOR PCR" />
      <v-data-table :headers="headers" :items="fichas" :loading="loading_table" :items-per-page="15" hide-default-footer>
        <template v-slot:item.contador="{item}">
          <small>{{ item.contador }}</small>
        </template>
        <template v-slot:item.nom_completo="{item}">
          <template v-if="item.paciente_isos">
            <small>{{ item.paciente_isos.nom_completo }}</small>
            <v-icon color="red darken-2" small v-if="item.antecedentes_ep.length > 0 && item.antecedentes_ep[0].dias_viaje">mdi-wallet-travel</v-icon>
            <template v-if="item.pcr_prueba_molecular">
              <v-chip small v-if="item.pcr_prueba_molecular.hora_fin" color="indigo" dark>
                <v-icon small left>mdi-test-tube</v-icon>FIN
              </v-chip>
              <v-chip v-else @click="accionesFinishPM(item.pcr_prueba_molecular.idpcr_pruebas_moleculares)" small dark color="orange darken-2">
                <v-icon small>mdi-test-tube</v-icon>
              </v-chip>
            </template>
            <template v-else>
              <v-btn @click="accionesStorePM(item)" icon small><v-icon small>mdi-test-tube-empty</v-icon></v-btn>
            </template>
          </template>
        </template>
        <template v-slot:item.nom_empresa="{item}">
          <small v-if="item.paciente_isos && item.paciente_isos.empresa">
            {{ item.paciente_isos.empresa.descripcion }}
          </small>
        </template>
        <template v-slot:item.f1="{item}">
          <template v-if="item.pcr_prueba_molecular">
            <template
              v-if="item.pcr_prueba_molecular.ficha_investigacion &&
                            item.pcr_prueba_molecular.ficha_investigacion.ficha_inv_foto &&
                            item.pcr_prueba_molecular.ficha_investigacion.ficha_inv_foto.path"
            >
              <v-btn icon small @click="verFoto('api/v1/fi/' + item.pcr_prueba_molecular.ficha_investigacion.ficha_inv_foto.path)">
                <v-icon small>mdi-eye</v-icon></v-btn>
              <v-btn icon small @click="accionesDeleteF1(item.pcr_prueba_molecular.ficha_investigacion.ficha_inv_foto)"><v-icon small>mdi-delete</v-icon></v-btn>
            </template>
            <template v-else>
              <v-btn @click="accionesUploadF1(item.pcr_prueba_molecular.ficha_investigacion.idinv_ficha)" icon small><v-icon small>mdi-camera</v-icon></v-btn>
            </template>
          </template>
        </template>
        <template v-slot:item.f2="{item}">
          <template v-if="item.pcr_prueba_molecular &&
                            item.pcr_prueba_molecular.ficha_investigacion &&
                            item.pcr_prueba_molecular.ficha_investigacion.ficha_inv_foto &&
                            (item.pcr_prueba_molecular.ficha_investigacion.ficha_inv_foto.path ||
                            item.pcr_prueba_molecular.ficha_investigacion.ficha_inv_foto.path2)"
          >
            <template
              v-if="item.pcr_prueba_molecular.ficha_investigacion &&
                            item.pcr_prueba_molecular.ficha_investigacion.ficha_inv_foto &&
                            item.pcr_prueba_molecular.ficha_investigacion.ficha_inv_foto.path2"
            >
              <v-btn icon small @click="verFoto('api/v1/fi2/' + item.pcr_prueba_molecular.ficha_investigacion.ficha_inv_foto.path2)">
                <v-icon small>mdi-eye</v-icon></v-btn>
              <v-btn icon small @click="accionesDeleteF2(item.pcr_prueba_molecular.ficha_investigacion.ficha_inv_foto)"><v-icon small>mdi-delete</v-icon></v-btn>
            </template>
            <template v-else>
              <v-btn @click="accionesUploadF2(item.pcr_prueba_molecular.ficha_investigacion.ficha_inv_foto.idinv_ficha_foto)" icon small><v-icon small>mdi-camera</v-icon></v-btn>
            </template>
          </template>
        </template>
        <template v-slot:item.ws="{item}">
          <template v-if="item.pcr_prueba_molecular">
            <v-btn icon small v-if="item.pcr_prueba_molecular && item.pcr_prueba_molecular.pcr_envio_munoz"
                   @click="imprimirBarcode(item)"><v-icon small>mdi-printer</v-icon>
            </v-btn>
            <v-btn :loading="loading" v-else @click="enviarMunoz(item)" icon small>
              <v-icon small>mdi-plus-circle-outline</v-icon>
            </v-btn>
          </template>
        </template>
      </v-data-table>
      <update-fab-button :store="modulo" />
      <paginate :store="modulo" />
    </v-card>
    <SelectEstacion :modulo="modulo" />
    <DialogUploadPhoto :store="modulo" :resource="recurso" :title="title" />
    <DialogBarcode :transaction_id="transaction_id" :nombre_completo="nombre_completo" :dni="dni" />
  </div>
</template>

<script>
import Viewer from "v-viewer/src/component.vue";
import { mapState } from 'vuex';
import SelectEstacion from "../SelectEstacion";
import paginate from "../paginate";
import buscador from "../buscador";
import DialogUploadPhoto from "../DialogUploadPhoto";
import DialogBarcode from "./DialogBarcode";
import UpdateFabButton from "../UpdateFabButton";
export default {
  components: {
    SelectEstacion,
    Viewer,
    buscador,
    paginate,
    DialogUploadPhoto,
    DialogBarcode,
    UpdateFabButton,
  },
  data() {
    return {
      modulo: "pcr",
      recurso: "",
      title: "",
      headers: [
        { text: 'N°', align: 'start', value: 'contador', sortable: false  },
        { text: 'Nombres', value: 'nom_completo', sortable: false },
        { text: 'Empresa', value: 'nom_empresa', sortable: false },
        { text: 'F1', value: 'f1', sortable: false },
        { text: 'F2', value: 'f2', sortable: false },
        { text: 'WS', value: 'ws', sortable: false },
      ],
      images: [],
      transaction_id: null,
      nombre_completo: null,
      dni: null,
    }
  },
  methods: {
    imprimirBarcode(ficha) {
      this.transaction_id = ficha.pcr_prueba_molecular.pcr_envio_munoz.transaction_id;
      this.dni = ficha.paciente_isos.numero_documento;
      this.nombre_completo = ficha.paciente_isos.nom_completo;
      this.$store.commit('pcr/SHOW_DIALOG_BARCODE', true);
    },
    enviarMunoz(ficha) {
      let r = confirm("Esta seguro de generar el codigo de barras?");
      if (r) this.$store.dispatch('pcr/storeMunoz', ficha);
    },
    accionesStorePM(ficha) {
      let r = confirm("Esta seguro de crear una prueba molecular?");
      if (r) this.$store.dispatch('pcr/storePM', ficha);
    },
    accionesFinishPM(id_pcr) {
      let r = confirm("Esta seguro de finalizar la prueba?");
      if (r) this.$store.dispatch('pcr/finishPM', id_pcr);
    },
    accionesUploadF1(id_fi) {
      this.$store.commit('SET_PHOTO', undefined);
      this.resetChildrenRefs();
      this.$store.commit('pcr/SET_ID_FI', id_fi);
      this.recurso = "F1";
      this.title = "FICHA INVESTIGACION ANVERSO";
      this.$store.commit('SHOW_DIALOG_UPLOAD_FOTO', true);
    },
    accionesDeleteF1(fi) {
      let r = confirm("Esta seguro de borrar la foto?");
      if (r) this.$store.dispatch('pcr/deleteF1', fi);
    },
    accionesUploadF2(id_foto_fi) {
      this.$store.commit('SET_PHOTO', undefined);
      this.resetChildrenRefs();
      this.$store.commit('pcr/SET_ID_FOTO_FI', id_foto_fi);
      this.recurso = "F2";
      this.title = "FICHA INVESTIGACION REVERSO";
      this.$store.commit('SHOW_DIALOG_UPLOAD_FOTO', true);
    },
    accionesDeleteF2(fi) {
      let r = confirm("Esta seguro de borrar la foto?");
      if (r) this.$store.dispatch('pcr/deleteF2', fi);
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
  },
  computed: {
    ...mapState(['id_estacion']),
    ...mapState('pcr',['loading_table','loading']),
    fichas: {
      get() {
        return this.$store.state.pcr.data.data;
      }
    },
  },
  created() {
    if (!this.id_estacion) this.$store.commit('SHOW_DIALOG_ESTACION', true);
    this.$store.dispatch('pcr/getFichas', 1);
  }
}
</script>

<style>
.image{
  display: none;
}
</style>
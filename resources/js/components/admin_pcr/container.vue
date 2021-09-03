<template>
  <div>
    <v-snackbar
      text
      transition="scale-transition"
      top
      :color="snackbar.color"
      v-model="snackbar.show"
      timeout="3000"
    >
      {{ snackbar.message }}
    </v-snackbar>
    <viewer :images="images" @inited="inited" class="viewer" ref="viewer">
      <img v-for="(src, i) in images" :src="src" :key="i" class="image" />
    </viewer>
    <v-card outlined>
      <v-card-title>LISTADO DE LLAMADAS NEXOGY API</v-card-title>
      <vuetify-audio
        :file="file"
        color="blue"
        :ended="audioFinish"
        :canPlay="audioInit"
        autoPlay
        downloadable
      ></vuetify-audio>

      <v-data-table
        :headers="headers"
        :items="fichas"
        :loading="loading_table"
        :items-per-page="15"
        class="mt-12"
        hide-default-footer
        :page.sync="getCurrentPage"
      >
        <template v-slot:header.callID="{ header }">
          <filtro-fecha :module="modulo" />
        </template>
        <template v-slot:header.paciente_isos.full_name="{ header }">
          <filtro-buscar :module="modulo" />
        </template>
        <template v-slot:header.sede="{ header }">
          <filtro-sede :module="modulo" />
        </template>
        <template v-slot:header.empresa="{ header }">
          <filtro-empresa :module="modulo" />
        </template>
        <template v-slot:header.tipo="{ header }">
          <filtro-tipo :module="modulo" />
        </template>
        <template v-slot:header.res="{ header }">
          <filtro-resultado-pcr :module="modulo" />
        </template>
        <template v-slot:item.contador="{ item }">
          <small>{{ item.contador }}</small>
        </template>
        <template v-slot:item.fecha="{ item }">
          <small>{{ item.fecha }}</small>
        </template>
        <template v-slot:item.paciente_isos.full_name="{ item }">
          <small>{{ item.paciente_isos.full_name }}</small>
        </template>
        <template v-slot:item.sede="{ item }">
          <small>{{ item.estacion.sede.descripcion }}</small>
        </template>
        <template v-slot:item.tid="{ item }">
          <template
            v-if="
              item.pcr_prueba_molecular &&
              item.pcr_prueba_molecular.pcr_envio_munoz
            "
          >
            <small>{{
              item.pcr_prueba_molecular.pcr_envio_munoz.transaction_id
            }}</small>
          </template>
        </template>
        <template v-slot:item.empresa="{ item }">
          <template v-if="item.paciente_isos && item.paciente_isos.empresa">
            <small>{{ item.paciente_isos.empresa.descripcion }}</small>
          </template>
        </template>
        <template v-slot:item.tipo="{ item }">
          <template v-if="item.pcr_prueba_molecular">
            <small v-if="item.pcr_prueba_molecular.tipo === 1"
              >CERRO VERDE</small
            >
            <small v-else-if="item.pcr_prueba_molecular.tipo === 2"
              >PARTICULARES</small
            >
            <small v-else>ASUMIDO ISOS</small>
          </template>
        </template>
        <template v-slot:item.precio="{ item }">
          <template v-if="item.pcr_prueba_molecular">
            <small>${{ item.pcr_prueba_molecular.precio }}</small>
          </template>
        </template>
        <template v-slot:item.res="{ item }">
          <template
            v-if="
              item.pcr_prueba_molecular &&
              item.pcr_prueba_molecular.resultado !== null
            "
          >
            <v-chip
              small
              dark
              color="green darken-2"
              v-if="item.pcr_prueba_molecular.resultado === 0"
              >NEG</v-chip
            >
            <v-chip
              small
              dark
              color="red darken-2"
              v-else-if="item.pcr_prueba_molecular.resultado === 1"
              >POS</v-chip
            >
            <v-chip
              small
              dark
              color="orange darken-2"
              v-else-if="item.pcr_prueba_molecular.resultado === 2"
              >ANULADO</v-chip
            >
          </template>
        </template>
        <template v-slot:item.foto="{ item }">
          <template v-if="item.pcr_prueba_molecular.resultado === 1">
            <template v-if="item.pcr_prueba_molecular.pcr_foto_muestra">
              <v-btn
                @click="verCurva(item.pcr_prueba_molecular.pcr_foto_muestra)"
                small
                icon
                ><v-icon small>mdi-eye</v-icon></v-btn
              >
              <v-btn
                @click="
                  eliminarFoto(item.pcr_prueba_molecular.pcr_foto_muestra.id)
                "
                small
                icon
                ><v-icon small>mdi-delete</v-icon></v-btn
              >
            </template>
            <template v-else>
              <v-btn @click="uploadPhoto(item)" icon small
                ><v-icon small>mdi-camera</v-icon></v-btn
              >
            </template>
          </template>
        </template>
        <template v-slot:item.actions="{ item }">
          <v-btn icon small @click="verFoto('/api/v1/dj/' + item.CallId)">
            <v-icon small>mdi-play</v-icon>
          </v-btn>
         
        </template>
      </v-data-table>
      <v-dialog max-width="600px" v-model="dialog.edit" persistent>
        <v-card>
          <v-card-title>EDITAR PRUEBA MOLECULAR</v-card-title>
          <v-divider></v-divider>
          <v-card-text>
            <validation-observer ref="observer" v-slot="{ validate }">
              <v-text-field
                prepend-icon="mdi-account"
                label="PACIENTE"
                disabled
                v-model="form.nom_completo"
              ></v-text-field>
              <v-text-field
                prepend-icon="mdi-domain"
                label="EMPRESA"
                disabled
                v-model="form.empresa"
              ></v-text-field>
              <validation-provider
                v-slot="{ errors }"
                name="price"
                rules="required|double"
              >
                <v-text-field
                  messages="El precio debe de contener decimales"
                  clearable
                  :error-messages="errors"
                  prepend-icon="mdi-currency-usd"
                  label="PRECIO"
                  v-model="form.precio"
                ></v-text-field>
              </validation-provider>
              <validation-provider
                v-slot="{ errors }"
                name="type"
                rules="required|integer"
              >
                <v-select
                  v-model="form.tipo"
                  :items="tipos"
                  item-text="descripcion"
                  item-value="idtipo_prueba_molecular"
                  prepend-icon="mdi-test-tube-empty"
                  @change="setPrecio"
                  :error-messages="errors"
                  label="TIPO"
                ></v-select>
              </validation-provider>
              <v-select
                v-model="form.resultado"
                :items="resultados"
                prepend-icon="mdi-test-tube"
                item-text="text"
                item-value="value"
                @change="changeRes"
                label="RESULTADO"
              ></v-select>
              <v-text-field
                messages="Ingrese detalle de ingreso de resultado"
                v-if="form.resultado !== null"
                clearable
                prepend-icon="mdi-comment"
                label="COMENTARIO"
                v-model="form.detalle"
              ></v-text-field>
            </validation-observer>
            <v-checkbox
              v-model="form.enviar_mensaje"
              label="Enviar Mensaje de Whatsapp"
            ></v-checkbox>
          </v-card-text>
          <v-divider></v-divider>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn text color="normal" @click="dialog.edit = false"
              >CANCELAR</v-btn
            >
            <v-btn :loading="loading" color="primary" @click="editPM"
              >GUARDAR</v-btn
            >
          </v-card-actions>
        </v-card>
      </v-dialog>
      <paginate :store="modulo" />
      <DialogReporte :store="modulo" />
      <DialogUploadFotoPcr :id_pcr="id_pcr" />
      <DialogVerCurva :curva="curva" />
    </v-card>
  </div>
</template>

<script>
import { mapState, mapMutations, mapActions, mapGetters } from "vuex";
import paginate from "../paginate";
import DialogReporte from "../DialogReporte";
import buscadorPcrSalud from "../buscadorPcrSalud";
import UpdateFabButton from "../UpdateFabButton";
import DialogUploadFotoPcr from "./DialogUploadFotoPcr";
import Viewer from "v-viewer/src/component.vue";
import FiltroFecha from "../headers/FiltroFecha";
import FiltroBuscar from "../headers/FiltroBuscar";
import FiltroSede from "../headers/FiltroSede";
import FiltroEmpresa from "../headers/FiltroEmpresa";
import FiltroTipo from "../headers/FiltroTipo";
import FiltroResultadoPcr from "../headers/FiltroResultadoPcr";
import DialogVerCurva from "../inteligencia_sanitaria/DialogVerCurva";

export default {
  components: {
    DialogVerCurva,
    paginate,
    DialogReporte,
    buscadorPcrSalud,
    UpdateFabButton,
    DialogUploadFotoPcr,
    Viewer,
    FiltroFecha,
    FiltroBuscar,
    FiltroSede,
    FiltroEmpresa,
    FiltroTipo,
    FiltroResultadoPcr,
    VuetifyAudio: () => import("vuetify-audio"),
  },
  data() {
    return {
      modulo: "admin_pcr",
      file: "",
      headers: [
        { text: "N°", align: "start", value: "contador", sortable: false },
        { text: "CallID", value: "CallId", sortable: false },
        { text: "CallerID", value: "CallerID", sortable: false },
        { text: "CustomerInternalRef", value: "CustomerInternalRef", sortable: false },
        { text: "DTMF", value: "DTMF", sortable: false },
        { text: "DID", value: "DiD", sortable: false },
        { text: "Direction", value: "Direction", sortable: false },
        { text: "Duration", value: "Duration", sortable: false },
        { text: "Extension", value: "Extension", sortable: false },
        { text: "Flagged", value: "Flagged", sortable: false },
        { text: "PhoneNumber", value: "PhoneNumber", sortable: false },
        { text: "Fecha/Hora", value: "StartTime", sortable: false },
        { text: "Acciones", value: "actions", sortable: false },
      ],
      form: {
        nom_completo: null,
        empresa: null,
        tipo: null,
        resultado: null,
        precio: null,
        id_pm: null,
        detalle: null,
        enviar_mensaje: false,
        idficha_paciente: null,
      },
      id_pcr: null,
      images: [],
      resultados: [
        { value: 0, text: "NEGATIVO" },
        { value: 1, text: "POSITIVO" },
        { value: 2, text: "ANULADO" },
        { value: 3, text: "SIN RESULTADO" },
      ],
      curva: {},
    };
  },
  methods: {
    ...mapMutations("admin_pcr", [
      "SHOW_DIALOG_UPLOAD_FOTO_MUESTRA",
      "SET_FECHA_FIN",
      "SET_FECHA_INICIO",
      "SET_RESULTADO",
      "SET_EMPRESA",
      "SET_SEDE",
    ]),
    ...mapActions("admin_pcr", ["deletePhoto", "getFichas"]),
    audioFinish()
    {
      console.log('audiofinal');
    },
    audioInit()
    {
      console.log('audioinicio');
    },
    verCurva(curva) {
      this.curva = Object.assign({}, curva);
      this.$store.commit("is/SHOW_DIALOG_CURVA", true);
    },
    accionesEliminarPM(ficha) {
      let r = confirm(
        `Esta seguro de eliminar la prueba molecular del paciente ${ficha.paciente_isos.nom_completo}?, al eliminar se borrarán las fotos de la ficha de investigación y trasansaction id`
      );
      if (r)
        this.$store.dispatch(
          "admin_pcr/deletePM",
          ficha.pcr_prueba_molecular.idpcr_pruebas_moleculares
        );
    },
    verFoto(ruta){
      console.log(ruta);
     // this.images = [];
      //this.images.push(ruta);
      //this.$viewer.show();
      this.file = ruta+'.wav';
      console.log(this.file);
    },
    accionesEditarPM(ficha) {
      /*this.form.id_pm = ficha.pcr_prueba_molecular.idpcr_pruebas_moleculares;
      this.form.nom_completo = ficha.paciente_isos.nom_completo;
      this.form.empresa = ficha.paciente_isos.empresa.descripcion;
      this.form.tipo = ficha.pcr_prueba_molecular.tipo;
      this.form.precio = ficha.pcr_prueba_molecular.precio;
      this.form.resultado = ficha.pcr_prueba_molecular.resultado;
      this.form.detalle = ficha.pcr_prueba_molecular.detalle;
      this.$store.commit("admin_pcr/SHOW_DIALOG_EDIT_PCR", true);*/


    },
    editPM() {
      this.$refs.observer.validate().then((isValid) => {
        if (isValid) {
          this.$store.dispatch("admin_pcr/updatePM", this.form);
        }
      });
    },
    setPrecio(tipo) {
      if (tipo === 3) this.form.precio = "0.00";
      else if (tipo === 1) this.form.precio = "110.00";
    },
    changeRes(res) {
      if (res === 2) this.form.precio = "0.00";
    },
    uploadPhoto({ pcr_prueba_molecular }) {
      this.id_pcr = pcr_prueba_molecular.idpcr_pruebas_moleculares;
      this.SHOW_DIALOG_UPLOAD_FOTO_MUESTRA(true);
    },
    eliminarFoto(id) {
      const r = confirm("Esta seguro de eliminar la foto?");
      if (r) this.deletePhoto(id);
    },
    
    inited(viewer) {
      this.$viewer = viewer;
    },
    show() {
      this.$viewer.show();
    },
  },
  computed: {
    ...mapState(["snackbar", "loading"]),
    ...mapState("currentUser", ["user"]),
    ...mapState("admin_pcr", ["loading_table", "tipos", "dialog"]),
    ...mapGetters("admin_pcr", ["getCurrentPage"]),
    fichas: {
      get() {
        return this.$store.state.admin_pcr.data.data;
      },
    },
  },
  created() {
    this.getFichas(1);
    this.$store.dispatch("getSedes");
  },
};
</script>

<style>
.image {
  display: none;
}
</style>

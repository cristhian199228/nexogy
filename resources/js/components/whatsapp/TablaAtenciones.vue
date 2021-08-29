<template>
  <div>
    <viewer :images="images"
            @inited="inited"
            class="viewer" ref="viewer"
    >
      <img v-for="(src,i) in images" :src="src" :key="i" class="image" />
    </viewer>
    <v-card outlined>
      <v-card-title>
        HISTORIAL DE ATENCIONES
      </v-card-title>
      <v-data-table v-if="arrFichas.length > 0" :headers="headers" :items="arrFichas" :items-per-page="10">
        <template v-slot:item.fecha2="{item}">
          <small>{{ item.fecha2 }}</small>
        </template>

        <template v-slot:item.a3="{item}">
          <template v-if="item.anexo_tres.length > 0 && item.anexo_tres[0].path">
            <v-btn icon small @click="verFoto(item.anexo_tres[0].path, 1)"><v-icon small>mdi-eye</v-icon></v-btn>
          </template>
        </template>

        <template v-slot:item.ct="{item}">
          <template v-if="item.declaracion_jurada.length > 0 && item.declaracion_jurada[0].path">
            <v-btn icon small @click="verFoto(item.declaracion_jurada[0].path, 2)"><v-icon small>mdi-eye</v-icon></v-btn>
          </template>
        </template>

        <template v-slot:item.ci="{item}">
          <template v-if="item.consentimiento_informado.length > 0 && item.consentimiento_informado[0].path">
            <v-btn icon small @click="verFoto(item.consentimiento_informado[0].path, 3)"><v-icon small>mdi-eye</v-icon></v-btn>
          </template>
        </template>

        <template v-slot:item.fi="{item}">
          <template v-if="item.pcr_prueba_molecular && item.pcr_prueba_molecular.ficha_investigacion &&
                                item.pcr_prueba_molecular.ficha_investigacion.ficha_inv_foto &&
                                item.pcr_prueba_molecular.ficha_investigacion.ficha_inv_foto.path &&
                                item.pcr_prueba_molecular.ficha_investigacion.ficha_inv_foto.path2"
          >
            <v-btn icon small @click="verFotoFI(item.pcr_prueba_molecular.ficha_investigacion.ficha_inv_foto)"><v-icon small>mdi-eye</v-icon></v-btn>
          </template>
        </template>

        <template v-slot:item.dc="{item}">
          <template v-if="item.datos_clinicos.length > 0">
            <v-chip dark small color="red darken-2">SI</v-chip>
            <v-btn icon small @click="openDialogDc(item.datos_clinicos[0])"><v-icon small>mdi-eye</v-icon></v-btn>
          </template>
          <template v-else>
            <v-chip dark small color="green darken-2">NO</v-chip>
          </template>
        </template>

        <template v-slot:item.ae="{item}">
          <template v-if="item.antecedentes_ep.length > 0">
            <v-chip dark small color="red darken-2">SI</v-chip>
            <v-btn icon small @click="openDialogAe(item.antecedentes_ep[0])"><v-icon small>mdi-eye</v-icon></v-btn>
          </template>
          <template v-else>
            <v-chip dark small color="green darken-2">NO</v-chip>
          </template>
        </template>

        <template v-slot:item.temp="{item}">
          <template v-if="item.temperatura.length > 0 && item.temperatura[0].valortemp ">
            <v-chip v-if="item.temperatura[0].valortemp > 37.8" dark small color="red darken-2">
              {{ item.temperatura[0].valortemp }}
            </v-chip>
            <v-chip v-else dark small color="green darken-2">
              {{ item.temperatura[0].valortemp }}
            </v-chip>
          </template>
        </template>

        <template v-slot:item.prs="{item}">
          <template v-if="item.prueba_serologica.length > 0">
            <template v-if="item.prueba_serologica[0].resultado">
              <v-chip small dark :color="getColor(item.prueba_serologica[0].resultado)">
                {{ getResult(item.prueba_serologica[0].resultado)}}
              </v-chip>
              <v-btn icon small @click="verConstancia(item.prueba_serologica[0].idpruebaserologicas, 'PRS')">
                <v-icon small>mdi-file-document</v-icon>
              </v-btn>
            </template>
          </template>
        </template>

        <template v-slot:item.ag="{item}">
          <template v-if="item.prueba_antigena.length > 0">
            <v-chip small dark :color="colorPa(item.prueba_antigena[0].resultado)">
              {{ resultadoPa(item.prueba_antigena[0].resultado)}}
            </v-chip>
            <v-btn icon small @click="verConstancia(item.prueba_antigena[0].id, 'AG')">
              <v-icon small>mdi-file-document</v-icon>
            </v-btn>
          </template>
        </template>

        <template v-slot:item.pcr="{item}">
          <template v-if="item.pcr_prueba_molecular && item.pcr_prueba_molecular.resultado !== null">
            <v-chip dark small :color="getColorPcr(item.pcr_prueba_molecular.resultado)">
              {{ getResultPcr(item.pcr_prueba_molecular.resultado) }}
            </v-chip>
            <v-btn icon small @click="verConstancia(item.pcr_prueba_molecular.idpcr_pruebas_moleculares, 'PCR')"
            ><v-icon small>mdi-file-document</v-icon></v-btn>
          </template>
        </template>
        <template v-slot:item.foto="{item}">
          <template v-if="item.pcr_prueba_molecular && item.pcr_prueba_molecular.pcr_foto_muestra">
            <v-btn @click="verCurva(item.pcr_prueba_molecular.pcr_foto_muestra)" small icon><v-icon small>mdi-eye</v-icon></v-btn>
          </template>
        </template>
      </v-data-table>

      <template v-else>
        <div class="text-center">
          NO SE REGISTRAN ATENCIONES
        </div>
      </template>
    </v-card>
    <DialogDC v-show="dialog.dc" @close-dialog="dialog.dc = false" :arr-dc="dc" :show-dialog="dialog.dc" />
    <DialogAE v-show="dialog.ae" @close-dialog="dialog.ae = false" :arr-ae="ae" :show-dialog="dialog.ae" />
    <DialogComentario v-show="dialog.comentario" @close-dialog="dialog.comentario = false" :comment="comentario" :show-dialog="dialog.comentario" />
    <DialogVerCurva :curva="curva" />
  </div>
</template>

<script>
import 'viewerjs/dist/viewer.css'
import Viewer from "v-viewer/src/component.vue"
import DialogDC from "./DialogDC";
import DialogAE from "./DialogAE";
import DialogComentario from "./DialogComentario";
import DialogVerCurva from "../inteligencia_sanitaria/DialogVerCurva";
import { mapGetters } from 'vuex'

export default {
  components: {
    Viewer,
    DialogDC,
    DialogAE,
    DialogComentario,
    DialogVerCurva,
  },
  props: {
    arrFichas: Array
  },
  data() {
    return {
      headers: [
        {text: "Fecha", align: "start", value: "fecha2"},
        {text: "A3", value: "a3", sortable: false},
        {text: "CT", value: "ct", sortable: false},
        {text: "CI", value: "ci", sortable: false},
        {text: "FI", value: "fi", sortable: false},
        {text: "DC", value: "dc", sortable: false},
        {text: "AE", value: "ae", sortable: false},
        {text: "T°", value: "temp", sortable: false},
        {text: "PRS", value: "prs", sortable: false},
        {text: "AG", value: "ag", sortable: false},
        {text: "PCR", value: "pcr", sortable: false},
        {text: "Curva pcr", value: "foto", sortable: false},
      ],
      images: [],
      dialog: {
        dc: false,
        ae: false,
        comentario: false,
      },
      dc: {},
      ae: {},
      comentario: null,
      curva: {},
    }
  },
  computed :{
    ...mapGetters('pa',[
      'resultadoPa',
      'colorPa'
    ]),
  },
  methods: {
    verCurva(curva) {
      this.curva = Object.assign({}, curva)
      this.$store.commit('is/SHOW_DIALOG_CURVA', true)
    },
    inited (viewer) {
      this.$viewer = viewer
    },
    show () {
      this.$viewer.show()
    },
    verConstancia(id, tipo) {
      window.open(`/descargarConstancia${tipo}/${id}`, "_blank");
    },
    getResult(resultado) {
      let res = "";
      switch (resultado) {
        case 1: res = "NEG"; break;
        case 2: res = "IGG R"; break;
        case 3: res = "IGG V"; break;
        case 4: res = "IGG"; break;
        case 5: res = "IGM"; break;
        case 6: res = "IGM/IGG"; break;
        case 7: res = "IGM P"; break;
        case 8: res = "IGM/IGG P"; break;
      }
      return res;
    },
    getResultPcr(resultado) {
      let res = "";
      switch (resultado) {
        case 0: res = "NEG"; break;
        case 1: res = "POS"; break;
        case 2: res = "ANULADO"; break;
      }
      return res;
    },
    getColorPcr(resultado) {
      let res = "";
      switch (resultado) {
        case 0: res = "green darken-2"; break;
        case 1: res = "red darken-2"; break;
        case 2: res = "orange darken-2"; break;
      }
      return res;
    },

    getColor(resultado) {
      let res = "";
      switch (resultado) {
        case 1: res = "green darken-2"; break;
        case 2:
        case 3: res = "orange darken-2"; break;
        case 4:
        case 5:
        case 6:
        case 7:
        case 8: res = "red darken-2"; break;
      }
      return res;
    },
    verFoto(path, tipo){
      this.images = [];
      let ruta = "";
      switch (tipo) {
        case 1: ruta = "/api/v1/a3/"; break;
        case 2: ruta = "/api/v1/dj/"; break;
        case 3: ruta = "/api/v1/ci/"; break;
        case 4: ruta = "api/v1/fotoPcr/";break;
      }
      this.$viewer.show();
      this.images.push(ruta + path);
    },
    verFotoFI(foto_inv){
      this.images = [];
      this.images.push("/api/v1/fi/" + foto_inv.path);
      this.images.push("/api/v1/fi2/" + foto_inv.path2);
      this.$viewer.show();
    },
    openDialogDc(dc) {
      this.dc = dc;
      this.dialog.dc = true;
    },
    openDialogAe(ae) {
      this.ae = ae;
      this.dialog.ae = true;
    },
    accionesComentario(comment){
      this.comentario = comment;
      this.dialog.comentario = true;
    }
  }
}
</script>

<style>
.image{
  display: none;
}
</style>

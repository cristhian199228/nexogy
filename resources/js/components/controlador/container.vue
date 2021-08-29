<template>
  <div>
    <viewer :images="images" @inited="inited" class="viewer" ref="viewer">
      <img v-for="(src, i) in images" :src="src" :key="i" class="image" />
    </viewer>
    <buscador :store="modulo" title="CONTROLADOR PRS" />
    <v-card v-for="ficha in data.data" :key="ficha.idficha_paciente" class="mb-5" outlined>
      <v-card-title style="padding-top: 5px;padding-bottom: 5px;" class="grey lighten-2 my-auto">
        <h6 class="font-weight-light">{{ ficha.contador }}. {{ ficha.paciente_isos.full_name }}&nbsp;&nbsp;&nbsp;</h6>
        <v-chip small class="grey darken-1" dark v-if="ficha.prueba_serologica.length > 0">{{ ficha.prueba_serologica[0].codigo_ps }}</v-chip>
        <v-btn small rounded color="primary" @click="crearPS(ficha)" class="ml-2"><v-icon left>mdi-plus</v-icon>PS</v-btn>
        <v-spacer></v-spacer>
        <template v-if="ficha.anexo_tres.length > 0">
          <v-btn small icon @click="verFoto('api/v1/a3/' + ficha.anexo_tres[0].path)"><v-icon small>mdi-eye</v-icon></v-btn>
          <v-btn small icon @click="accionesDeleteA3(ficha.anexo_tres[0])"><v-icon small>mdi-delete</v-icon></v-btn>
        </template>
        <template v-else>
          <v-btn small icon @click="accionesUploadA3(ficha.idficha_paciente)"><v-icon small>mdi-camera</v-icon></v-btn>
        </template>
      </v-card-title>
      <v-simple-table v-if="ficha.prueba_serologica.length > 0">
        <template v-slot:default>
          <thead>
          <tr>
            <th>Acciones</th>
            <th>Hora inicio</th>
            <th>Tiempo</th>
            <th>IGM</th>
            <th>IGG</th>
            <th>IGM/IGG</th>
            <th>Inicio/Fin</th>
          </tr>
          </thead>
          <tbody>
          <tr v-for="prueba in ficha.prueba_serologica" :key="prueba.idpruebaserologicas">
            <td>
              <v-btn small icon @click="borrarPS(prueba.idpruebaserologicas)"><v-icon small>mdi-delete</v-icon></v-btn>
              <v-btn small v-if="prueba.hora_fin" icon @click="accionesGuardarPS(prueba)"><v-icon small>mdi-pencil</v-icon></v-btn>
              <template v-if="prueba.hora_fin">
                <v-btn small icon :color="prueba.envio_w_p_count > 0 ? 'green darken-2': ''" :disabled="!ficha.enviar_mensaje"
                       @click="enviarWhatsapp(prueba)"><v-icon small>mdi-whatsapp</v-icon></v-btn>
              </template>
            </td>
            <td>
              <small v-if="prueba.hora_inicio">
                {{prueba.hora_inicio.slice(10, prueba.hora_inicio.length - 3) }}
              </small>
            </td>
            <td>
              <template v-if="prueba.hora_inicio">
                <small>
                  <time-ago :refresh="5" :datetime="prueba.hora_inicio" :todo="() => tiempoPrueba(prueba)"></time-ago>
                </small>
              </template>
            </td>
            <td>
              <template v-if="prueba.p1_react1gm !== null && !prueba.invalido">
                <v-chip small dark color="red darken-2" v-if="prueba.p1_react1gm">SI</v-chip>
                <v-chip small dark color="green darken-2" v-else>NO</v-chip>
              </template>
            </td>
            <td>
              <template v-if=" prueba.p1_reactigg !== null && !prueba.invalido">
                <v-chip small dark color="red darken-2" v-if="prueba.p1_reactigg">SI</v-chip>
                <v-chip small dark color="green darken-2" v-else>NO</v-chip>
              </template>
            </td>
            <td>
              <template v-if="prueba.p1_reactigm_igg !== null && !prueba.invalido">
                <v-chip small dark color="red darken-2" v-if="prueba.p1_reactigm_igg">SI</v-chip>
                <v-chip small dark color="green darken-2" v-else>NO</v-chip>
              </template>
            </td>
            <td>
              <v-btn v-if="!prueba.hora_inicio" icon @click="startPS(prueba.idpruebaserologicas)"><v-icon>mdi-play</v-icon></v-btn>
              <v-btn v-else-if="prueba.hora_inicio && !prueba.hora_fin" icon @click="accionesGuardarPS(prueba)"><v-icon>mdi-stop</v-icon></v-btn>
              <v-chip small dark color="orange darken-2" v-else-if="prueba.invalido">INVÁLIDO</v-chip>
              <v-chip small dark color="green darken-2" v-else>COMPLETO</v-chip>
            </td>
          </tr>
          </tbody>
        </template>
      </v-simple-table>
    </v-card>
    <paginate :store="modulo" />
    <update-fab-button :store="modulo" />
    <SelectEstacion :modulo="modulo" />
    <DialogUploadPhoto :store="modulo" :resource="recurso" :title="title" />
    <UpdatePS :ps="ps" />
  </div>
</template>

<script>
import { mapState, mapGetters, mapActions, mapMutations } from "vuex";
import SelectEstacion from "../SelectEstacion";
import paginate from "../paginate";
import buscador from "../buscador";
import DialogUploadPhoto from "../DialogUploadPhoto";
import UpdateFabButton from "../UpdateFabButton";
import TimeAgo from "vue2-timeago";
import "viewerjs/dist/viewer.css";
import Viewer from "v-viewer/src/component.vue";
import UpdatePS from "./UpdatePS";

export default {
  components: {
    SelectEstacion,
    TimeAgo,
    Viewer,
    buscador,
    paginate,
    DialogUploadPhoto,
    UpdateFabButton,
    UpdatePS
  },
  data() {
    return {
      modulo: "controlador",
      recurso: "",
      title: "",
      images: [],
      ps: {}
    };
  },
  methods: {
    ...mapMutations("controlador", ["SET_ID_PS", "SHOW_DIALOG_UPDATE_PS"]),
    ...mapActions("controlador", [
      "getFichas",
      "storePS",
      "startPS",
      "updatePS"
    ]),
    accionesUploadA3(id_ficha) {
      this.$store.commit("SET_PHOTO", undefined);
      this.resetChildrenRefs();
      this.$store.commit("controlador/SET_ID_FICHA", id_ficha);
      this.recurso = "A3";
      this.title = "ANEXO TRES";
      this.$store.commit("SHOW_DIALOG_UPLOAD_FOTO", true);
    },
    borrarPS(id_ps) {
      let r = confirm("Esta seguro de borrar la pruebas serológica?");
      if (r) this.$store.dispatch("controlador/deletePS", id_ps);
    },
    accionesDeleteA3(a3) {
      let r = confirm("Esta seguro de borrar la foto?");
      if (r) this.$store.dispatch("controlador/deleteA3", a3);
    },
    crearPS(ficha) {
      let r = confirm("Esta seguro de crear una prueba serológica?");
      if (r) this.$store.dispatch("controlador/storePS", ficha);
    },
    enviarWhatsapp(prueba) {
      let r = confirm("Esta seguro que desea enviar el whatsapp?");
      if (r) this.$store.dispatch("controlador/storeWP", prueba);
    },
    accionesGuardarPS(prueba) {
      this.ps = Object.assign({}, prueba);
      this.SET_ID_PS(prueba.idpruebaserologicas);
      this.SHOW_DIALOG_UPDATE_PS(true);
    },
    tiempoPrueba(prueba) {
      let fechaEnMiliseg = Date.now();
      let date_2 = new Date(prueba.hora_inicio);
      let resultado = fechaEnMiliseg - date_2;
      let resultado_minutos = resultado / (1000 * 60);
      if (
        (resultado_minutos > 15 && resultado_minutos < 15.3) ||
        (resultado_minutos > 20 && resultado_minutos < 20.3)
      ) {
        let sound =
          "http://onj3.andrelouis.com/phonetones/unzipped/Meizu%20MX4/notifications/Meteor.mp3";
        let audio = new Audio(sound);
        audio.play();
        navigator.vibrate(2000);
      }
    },
    resetChildrenRefs() {
      if (this.$children && this.$children.length > 0) {
        for (let i = 0; i < this.$children.length; i++) {
          if (
            this.$children[i].$refs &&
            this.$children[i].$refs.photo
          ) {
            this.$children[i].$refs.photo.value = null;
          }
        }
      }
    },
    verFoto(ruta) {
      this.images = [];
      this.images.push(ruta);
      this.$viewer.show();
    },
    inited(viewer) {
      this.$viewer = viewer;
    },
    show() {
      this.$viewer.show();
    }
  },
  computed: {
    ...mapState(["id_estacion"]),
    ...mapState("controlador", ["data", "dialog"]),
    ...mapGetters(["nombreEstacion"]),
    buscar: {
      get() {
        return this.$store.state.controlador.buscar;
      },
      set(val) {
        this.$store.commit("controlador/SET_CRITERIO_BUSQUEDA", val);
        //this.$store.dispatch('controlador/getFichas', 1);
      }
    }
  },
  created() {
    if (!this.id_estacion) this.$store.commit("SHOW_DIALOG_ESTACION", true);
    this.getFichas(1);
  },
  watch: {
    categoria() {
      var sound =
        "http://onj3.andrelouis.com/phonetones/unzipped/Meizu%20MX4/notifications/Meteor.mp3";
      var audio = new Audio(sound);
      navigator.vibrate(2000);
    }
  }
};
</script>

<style>
.image {
  display: none;
}
</style>

<template>
  <div>
    <viewer :images="images" @inited="inited" class="viewer" ref="viewer">
      <img v-for="(src,i) in images" :src="src" :key="i" class="image" />
    </viewer>
    <buscador :store="modulo" :title="title" />
    <v-card v-for="ficha in fichas" :key="ficha.idficha_paciente" class="mb-5" outlined >
      <v-card-title class="indigo lighten-4 my-auto ma-0 px-2 py-1">
        <div class="d-flex">
          <p class="subtitle-1 my-auto font-weight-light ma-0">{{ ficha.contador }}. {{ ficha.paciente_isos.full_name }}</p>
          <v-btn @click="storePa(ficha.idficha_paciente)" color="indigo darken-4" class="my-auto ml-2" icon><v-icon>mdi-plus-circle</v-icon></v-btn>
        </div>
        <v-spacer></v-spacer>
        <template v-if="ficha.anexo_tres.length > 0">
          <v-btn small icon @click="verFoto('api/v1/a3/' + ficha.anexo_tres[0].path)"><v-icon small>mdi-eye</v-icon></v-btn>
          <v-btn small icon @click="accionesDeleteA3(ficha.anexo_tres[0])"><v-icon small>mdi-delete</v-icon></v-btn>
        </template>
        <template v-else>
          <v-btn small icon @click="accionesUploadA3(ficha.idficha_paciente)"><v-icon small>mdi-camera</v-icon></v-btn>
        </template>
      </v-card-title>
      <v-simple-table v-if="ficha.prueba_antigena.length > 0">
        <template v-slot:default>
          <thead>
          <tr>
            <th>Acciones</th>
            <th>Hora Inicio</th>
            <th>Tiempo</th>
            <th>Resultado</th>
            <th>Estado</th>
          </tr>
          </thead>
          <tbody>
          <tr v-for="pa in ficha.prueba_antigena" :key="pa.id">
            <td>
              <v-btn @click="deletePa(pa.id)" icon small><v-icon small>mdi-delete</v-icon></v-btn>
              <template v-if="pa.finished_at">
                <v-btn @click="updatePa(pa)" icon small><v-icon small>mdi-pencil</v-icon></v-btn>
                <v-btn small icon :color="pa.envio_count > 0 ? 'green darken-2' : ''" :disabled="!ficha.enviar_mensaje"
                       @click="enviarWp(pa.id)"><v-icon small>mdi-whatsapp</v-icon></v-btn>
              </template>
            </td>
            <td>
              <p v-if="pa.started_at" class="caption my-auto">{{pa.started_at.slice(10,(pa.started_at.length)-3)}}</p>
            </td>
            <td>
              <time-ago v-if="pa.started_at" locale="es" :refresh="5" :datetime="pa.started_at"></time-ago>
            </td>
            <td>
              <v-chip v-if="pa.resultado !== null" small dark
                      :color="colorPa(pa.resultado)">{{ resultadoPa(pa.resultado) }}</v-chip>
            </td>
            <td>
              <template v-if="pa.started_at && pa.finished_at">
                <v-chip color="green darken-2" dark small>COMPLETADO</v-chip>
              </template>
              <template v-else>
                <v-btn @click="updatePa(pa)" v-if="pa.started_at" icon><v-icon>mdi-stop</v-icon></v-btn>
                <v-btn @click="start(pa.id)" v-else icon><v-icon>mdi-play</v-icon></v-btn>
              </template>
            </td>
          </tr>
          </tbody>
        </template>
      </v-simple-table>
    </v-card>
    <update-fab-button :store="modulo" />
    <SelectEstacion :modulo="modulo" />
    <paginate :store="modulo" />
    <DialogSavePruebaAntingena :pa="pruebaAntigena" />
    <DialogUploadPhoto :store="modulo" resource="A3" title="ANEXO 3" />
  </div>
</template>

<script>
import buscador from "../buscador";
import SelectEstacion from "../SelectEstacion";
import paginate from "../paginate";
import DialogSavePruebaAntingena from "./DialogSavePruebaAntingena";
import UpdateFabButton from "../UpdateFabButton";
import TimeAgo from 'vue2-timeago';
import { mapState, mapGetters, mapActions } from 'vuex'
import 'viewerjs/dist/viewer.css'
import Viewer from "v-viewer/src/component.vue"
import DialogUploadPhoto from "../DialogUploadPhoto";

export default {
  components: {
    buscador,
    SelectEstacion,
    paginate,
    DialogSavePruebaAntingena,
    UpdateFabButton,
    TimeAgo,
    Viewer,
    DialogUploadPhoto,
  },
  data() {
    return {
      modulo: 'pa',
      title: 'CONTROLADOR AG',
      pruebaAntigena: {},
      images: [],
    }
  },
  methods: {
    ...mapActions('pa',[
      'store',
      'start',
      'delete',
      'getFichas'
    ]),
    storePa(idficha_paciente) {
      const res = confirm('Está seguro de crear una prueba antígena?')
      if (res) this.store(idficha_paciente)
    },
    /*startPa(id_pa) {
      const res = confirm('Está seguro de iniciar la prueba antígena?')
      if (res) this.start(id_pa)
    },*/
    deletePa(id_pa) {
      const res = confirm('Está seguro de eliminar la prueba antígena?')
      if (res) this.delete(id_pa)
    },
    updatePa(pruebaAntingena) {
      this.pruebaAntigena = Object.assign({}, pruebaAntingena)
      this.$store.commit('pa/SHOW_SAVE_PRUEBA_ANTIGENA_DIALOG', true)
    },
    accionesUploadA3(id_ficha) {
      this.$store.commit('SET_PHOTO', undefined);
      this.resetChildrenRefs();
      this.$store.commit('pa/SET_ID_FICHA', id_ficha);
      this.$store.commit('SHOW_DIALOG_UPLOAD_FOTO', true);
    },
    accionesDeleteA3(a3) {
      let r = confirm("Esta seguro de borrar la foto?");
      if (r) this.$store.dispatch('pa/deleteA3', a3);
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
    enviarWp(id) {
      const res = confirm('Esta seguro de enviar el mensaje de whatsapp?')
      if (res) this.$store.dispatch('pa/storeWP', id)
    }
  },
  computed: {
    ...mapState([
      'id_estacion'
    ]),
    ...mapGetters('pa',[
      'fichas',
      'currentPage',
      'resultadoPa',
      'colorPa'
    ]),
  },
  created() {
    if (!this.id_estacion) this.$store.commit('SHOW_DIALOG_ESTACION', true)
    this.getFichas(this.currentPage)
  }
}
</script>


<style>
.image{
  display: none;
}
</style>

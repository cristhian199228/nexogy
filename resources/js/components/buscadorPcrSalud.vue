<template>
  <div>
    <v-row class="d-flex px-5 py-2" dense>
      <h4 class="my-auto">{{ title }}</h4>
      <v-divider vertical class="mx-5"></v-divider>
      <template v-if="nombreEstacion">
        <v-chip label class="my-auto mr-5" @click="SHOW_DIALOG_ESTACION(true)" dark color="indigo">
          <v-avatar left><v-icon>mdi-map-marker</v-icon></v-avatar>{{ nombreEstacion }}
        </v-chip>
      </template>
      <v-btn class="my-auto" v-if="store !== 'admin_pcr'"
             dark color="success" @click="abrirReporte"><v-icon left>mdi-microsoft-excel</v-icon>REPORTE</v-btn>
      <v-spacer></v-spacer>
      <v-text-field
          class="my-auto"
          v-model="buscar"
          append-icon="mdi-magnify"
          single-line
          label="Search"
          @click:append="buscarFichas"
          @keyup.enter="buscarFichas"
      ></v-text-field>
    </v-row>
  </div>
</template>

<script>
import {mapGetters, mapMutations} from 'vuex'
export default {
  props: ['store', 'title'],
  computed: {
    ...mapGetters(['nombreEstacion','nombreSede']),
    buscar: {
      get() {
        return this.$store.state[this.store].buscar;
      },
      set (val) {
        this.$store.commit(this.store + '/SET_CRITERIO_BUSQUEDA', val);
      }
    }
  },
  methods: {
    ...mapMutations(['SHOW_DIALOG_ESTACION']),
    buscarFichas() {
      this.$store.dispatch(this.store + '/getFichas', 1);
    },
    abrirReporte() {
      this.$store.commit('SHOW_DIALOG_REPORTE', true);
    },
  }
}
</script>

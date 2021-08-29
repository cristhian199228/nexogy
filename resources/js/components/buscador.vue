<template>
  <div>
    <v-row class="d-flex px-5 py-2" dense>
      <h4 class="my-auto">{{ title }}</h4>
      <v-divider vertical class="mx-5"></v-divider>
      <v-chip label class="my-auto" v-if="store !== 'super_prs' && nombreEstacion" @click="SHOW_DIALOG_ESTACION(true)" dark color="indigo">
        <v-avatar left><v-icon>mdi-map-marker</v-icon></v-avatar>{{ nombreEstacion }}
      </v-chip>
      <template v-else-if="nombreSede">
        <v-chip label class="my-auto" @click="SHOW_DIALOG_SEDE(true)" dark color="indigo">
          <v-avatar left><v-icon>mdi-map-marker</v-icon></v-avatar>{{ nombreSede }}
        </v-chip>
        <v-chip label v-if="id_sede === 3" class="ml-3 my-auto" dark color="success" @click="generarReporte">
          <v-avatar left><v-icon>mdi-microsoft-excel</v-icon></v-avatar>REPORTE DIARIO
        </v-chip>
      </template>
      <v-spacer></v-spacer>
      <template v-if="store !== 'super_prs'">
        <v-text-field
          v-model="buscar"
          append-icon="mdi-magnify"
          single-line
          label="Search"
          @click:append="buscarFichas"
          @keyup.enter="buscarFichas"
        ></v-text-field>
      </template>
    </v-row>
  </div>
</template>

<script>
import { mapGetters, mapMutations, mapState } from 'vuex'
export default {
  props: ['store', 'title'],
  computed: {
    ...mapGetters(['nombreEstacion','nombreSede']),
    ...mapState(['id_sede']),
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
    ...mapMutations(['SHOW_DIALOG_ESTACION','SHOW_DIALOG_SEDE']),
    buscarFichas() {
      this.$store.dispatch(this.store + '/getFichas', 1);
    },
    generarReporte() {
      const conf = confirm("Está seguro de generar el reporte diario?");
      if (conf) window.open( '/api/v1/complejo', "_blank");
    }
  },
}
</script>

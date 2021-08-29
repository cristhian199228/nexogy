<template>
  <div>
    <v-dialog transition="scale-transition" persistent v-model="dialog.estacion" max-width="600px">
      <v-card>
        <v-card-title>Seleccione una estación</v-card-title>
        <v-card-text>
          <v-select
            v-model="estacion"
            :items="estaciones"
            item-text="nom_estacion"
            item-value="idestaciones"
            label="Seleccione una estación"
            outlined
            dense
          >
          </v-select>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text color="normal" @click="SHOW_DIALOG_ESTACION(false);">CANCELAR</v-btn>
          <v-btn color="primary" @click="seleccionarEstacion(estacion)">ACEPTAR</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import { mapState, mapMutations } from 'vuex'
export  default {
  props: {
    modulo: String,
  },
  data() {
    return{
      estacion: "",
    }
  },
  computed: {
    ...mapState(['dialog','estaciones'])
  },
  methods: {
    ...mapMutations(['SHOW_DIALOG_ESTACION','SET_ID_ESTACION']),
    seleccionarEstacion(estacion) {
      this.SET_ID_ESTACION(estacion);
      this.$store.dispatch(this.modulo + '/getFichas', 1);
      this.SHOW_DIALOG_ESTACION(false);
    }
  },
  created() {
    this.$store.dispatch('getEstaciones');
  }
}
</script>

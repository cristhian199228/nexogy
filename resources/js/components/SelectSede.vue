<template>
  <div>
    <v-dialog transition="scale-transition" persistent v-model="dialog.sede" max-width="600px">
      <v-card>
        <v-card-title>Seleccione una sede</v-card-title>
        <v-card-text>
          <v-select
            v-model="sede"
            :items="sedes"
            item-text="descripcion"
            item-value="idsedes"
            label="Seleccione una sede"
            outlined
            dense
          >
          </v-select>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text color="normal" @click="SHOW_DIALOG_SEDE(false);">CANCELAR</v-btn>
          <v-btn color="primary" @click="seleccionarSede(sede)">ACEPTAR</v-btn>
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
      sede: "",
    }
  },
  computed: {
    ...mapState(['dialog','sedes'])
  },
  methods: {
    ...mapMutations(['SHOW_DIALOG_SEDE','SET_ID_SEDE']),
    seleccionarSede(id) {
      this.SET_ID_SEDE(id);
      this.$store.dispatch(this.modulo + '/getFichas', 1);
      this.SHOW_DIALOG_SEDE(false);
    }
  },
  created() {
    this.$store.dispatch('getSedes');
  }
}
</script>

<template>
  <div>
    <v-dialog v-model="dialog" persistent max-width="600px">
      <v-card>
        <v-card-title>GUARDAR REEVALUACION</v-card-title>
        <v-divider></v-divider>
        <v-card-text>
          <v-select v-model="form.estado" :items="estados" disabled label="Estado"></v-select>
          <v-text-field v-model.number="form.dias_bloqueo" type="number" label="Días bloqueo"></v-text-field>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="normal" text @click="close">CANCELAR</v-btn>
          <v-btn color="primary" @click="guardar">GUARDAR</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import { mapState } from 'vuex'
export default {
  data() {
    return {
      form: {
        estado: 2,
        dias_bloqueo: null,
      }
    }
  },
  methods: {
    close() {
      this.$store.commit('is/SHOW_DIALOG_GUARDAR_REEVALUACION', false)
    },
    guardar() {
      this.$store.dispatch('is/guardarReevaluacion', this.form)
    }
  },
  computed: {
    ...mapState('is',['dialog'])
  }
}
</script>
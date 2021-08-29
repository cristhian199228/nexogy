<template>
  <div>
    <v-dialog v-model="dialog" persistent max-width="600px">
      <v-card>
        <v-card-title>ENVIAR XML POR CORREO</v-card-title>
        <v-divider></v-divider>
        <v-card-text>
          <v-combobox
            v-model="correos"
            :items="items"
            label="Destinatarios"
            multiple
            outlined
            dense
          ></v-combobox>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="normal" text @click="close">CANCELAR</v-btn>
          <v-btn color="primary" :loading="loading" :disabled="correos.length === 0 "  @click="enviar">ENVIAR</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import {mapState} from 'vuex'

export default {
  props: ['idficha_paciente'],
  data () {
    return {
      items: [
        'juan.sanchez@internationalsos.com',
        'jquispea2@fmi.com',
        'fzegarra@fmi.com',
        'npacheco@fmi.com',
        'pperalta1@fmi.com'
      ],
      correos: [
        'juan.sanchez@internationalsos.com',
        'jquispea2@fmi.com',
        'fzegarra@fmi.com',
        'npacheco@fmi.com'
      ],
    }
  },
  computed: {
    ...mapState('is',['loading']),
    dialog() {
      return this.$store.state.is.dialog_correo
    }
  },
  methods: {
    close() {
      this.$store.commit('is/SHOW_DIALOG_ENVIAR_CORREO', false)
    },
    enviar(){
      this.$store.dispatch('is/sendMail', {
        idficha_paciente: this.idficha_paciente,
        correos: this.correos,
      })
    }
  }
}
</script>
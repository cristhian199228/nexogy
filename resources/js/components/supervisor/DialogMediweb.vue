<template>
  <div>
    <v-dialog v-model="dialog.mediweb" max-width="500px" persistent>
      <v-card>
        <v-card-title>ESTADO PACIENTE</v-card-title>
        <v-card-text>
          <validation-observer ref="observer" v-slot="{validate}">
            <validation-provider v-slot="{errors}" name="Status" rules="required">
              <v-select
                v-model="estado_paciente_ws"
                :items="estado_paciente_ws_opt"
                dense
                outlined
                item-text="text"
                item-value="value"
                label="Seleccione el estado del paciente"
                :error-messages="errors"
              ></v-select>
            </validation-provider>
          </validation-observer>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text color="normal" @click="close">CANCELAR</v-btn>
          <v-btn color="primary" @click="enviarPacienteMw(estado_paciente_ws)" :loading="loading">ENVIAR</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import { required } from 'vee-validate/dist/rules'
import { extend, ValidationObserver, ValidationProvider, setInteractionMode } from 'vee-validate'
import { mapState } from 'vuex'
setInteractionMode('eager')
extend('required', {
  ...required,
  message: '{_field_} can not be empty',
})

export default {
  components: {
    ValidationObserver,
    ValidationProvider,
  },
  props: [],
  data() {
    return {
      estado_paciente_ws: "",
      estado_paciente_ws_opt : [
        {value: "RN", text: "REACTIVO NUEVO"},
        {value: "RP", text: "REACTIVO PERSISTENTE"},
        {value: "RR", text: "REACTIVO RECUPERADO"},
        {value: "NR", text: "NO REACTIVO"},
      ],
    }
  },
  methods: {
    close() {
      this.$refs.observer.reset();
      this.$store.commit('super_prs/SHOW_DIALOG_MEDIWEB', false);
    },
    enviarPacienteMw(estado) {
      this.$refs.observer.validate().then(valid => {
        if(valid) {
          this.$store.dispatch('super_prs/enviarMw', estado);
        }
      })
    }
  },
  computed: {
    ...mapState('super_prs',['dialog','loading'])
  }

}
</script>

<template>
  <div>
    <v-dialog scrollable persistent max-width="600px" v-model="showDialog">
      <v-card>
        <v-card-title>DATOS CLÍNICOS</v-card-title>
        <v-divider></v-divider>
        <v-card-text style="height:550px;">
          <validation-observer ref="observer" v-slot="{validate}">
            <v-switch hide-details v-model="dc.tos" label="Tos"></v-switch>
            <v-switch hide-details v-model="dc.dolor_garganta" label="Dolor de Garganta"></v-switch>
            <v-switch hide-details v-model="dc.congestion_nasal" label="Congestión nasal"></v-switch>
            <v-switch hide-details v-model="dc.dificultad_respiratoria" label="Dificultad Respiratoria"></v-switch>
            <v-switch hide-details v-model="dc.fiebre" label="Fiebre"></v-switch>
            <v-switch hide-details v-model="dc.malestar_general" label="Malestar General"></v-switch>
            <v-switch hide-details v-model="dc.diarrea" label="Diarrea"></v-switch>
            <v-switch hide-details v-model="dc.nauseas_vomitos" label="Náuseas y vómitos"></v-switch>
            <v-switch hide-details v-model="dc.cefalea" label="Cefálea"></v-switch>
            <v-switch hide-details v-model="dc.irritabilidad_confusion" label="Irritabilidad y confusión"></v-switch>
            <v-switch hide-details v-model="dc.falta_aliento" label="Falta de aliento"></v-switch>
            <v-switch v-model="dc.anosmia_ausegia" label="Anosmia o Ausegia"></v-switch>
            <v-text-field v-model="dc.otros" label="Otros síntomas"></v-text-field>
            <v-text-field v-model="dc.toma_medicamento" label="Toma algún medicamento"></v-text-field>
            <v-menu
              v-model="menu2"
              :close-on-content-click="false"
              :nudge-right="40"
              transition="scale-transition"
              offset-y
              min-width="auto"
            >
              <template v-slot:activator="{ on, attrs }">
                <validation-provider v-slot="{errors}" name="fecha inicio de síntomas" rules="required">
                  <v-text-field
                    v-model="dc.fecha_inicio_sintomas"
                    label="Fecha de inicio de síntomas"
                    prepend-icon="mdi-calendar"
                    readonly
                    v-bind="attrs"
                    v-on="on"
                    :error-messages="errors"
                  ></v-text-field>
                </validation-provider>
              </template>
              <v-date-picker
                v-model="dc.fecha_inicio_sintomas"
                @input="menu2 = false"
              ></v-date-picker>
            </v-menu>
            <v-switch hide-details v-model="dc.post_vacunado" label="SINTOMATICO POST VACUNACIÓN"></v-switch>
          </validation-observer>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text color="normal" @click="close">ATRAS</v-btn>
          <v-btn color="primary" :loading="loading" @click="guardar">GUARDAR</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>

export default {
  props: {
    dc: {
      type: Object,
    },
    showDialog: {
      type: Boolean,
      default: false,
    },
    action: {
      type: String
    }
  },
  data() {
    return {
      menu2: false,
      loading: false,
    }
  },
  methods: {
    guardar() {
      this.$refs.observer.validate().then(async isValid => {
        if (!isValid) {
          return
        }
        this.loading = true
        try {
          const res = await this.$store.dispatch(`admision/${this.action}DC`, this.dc)
          this.$store.commit('SHOW_SUCCESS_SNACKBAR', await res.data.message)
          this.loading = false
          this.close()
          await this.$store.dispatch('admision/getFichas', this.$store.getters["admision/currentPage"])
        } catch (e) {
          this.loading = false
          this.$store.commit('SHOW_ERROR_SNACKBAR', await e.response.data.message)
        }
      })
    },
    close() {
      this.$emit('close-dialog')
      this.reset()
    },
    reset() {
      for (const prop in this.dc) {
        if (prop === 'idfichapacientes' || prop === 'otros' ||
          prop === 'toma_medicamento' || prop === 'fecha_inicio_sintomas') {
          this.dc[prop] = null
        } else {
          this.dc[prop] = false
        }
      }
      this.$refs.observer.reset()
    }
  }


}
</script>

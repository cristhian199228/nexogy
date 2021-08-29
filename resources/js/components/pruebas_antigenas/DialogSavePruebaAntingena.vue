<template>
  <div>
    <validation-observer ref="observer" v-slot="{validate}">
      <v-dialog v-model="dialog" max-width="500px" persistent scrollable>
        <v-card>
          <v-card-title>GUARDAR PRUEBA ANTÍGENA</v-card-title>
          <v-divider></v-divider>
          <v-card-text style="max-height: 600px;">
            <p class="subtitle-1">Procedencia de la solicitud de diagnóstico</p>
            <v-checkbox v-model="pa.llamada_113" label="Llamada al 113" hide-details></v-checkbox>
            <v-checkbox v-model="pa.prueba_eess" label="Prueba de EESS" hide-details></v-checkbox>
            <v-checkbox v-model="pa.personal_salud" label="Personal de salud" hide-details></v-checkbox>
            <v-checkbox v-model="pa.contacto_caso_confirmado" label="Contacto con caso confirmado" hide-details></v-checkbox>
            <v-checkbox v-model="pa.contacto_caso_sospechoso" label="Contacto con caso sospechoso" hide-details></v-checkbox>
            <v-checkbox v-model="pa.persona_extranjero" label="Persona proveniente del extranjero (migraciones)" hide-details></v-checkbox>
            <v-checkbox v-model="pa.persona_conglomerados" label="Persona que vive, trabaja o asiste a conglomerados" hide-details></v-checkbox>
            <v-text-field v-model="pa.otros" label="Otros"></v-text-field>
            <validation-provider v-slot="{errors}" name="ccs" rules="required">
              <v-select v-model="pa.clasificacion_clinica_severidad" label="Clasificación clínica de la severidad"
                        :items="clasificaciones" item-text="text" item-value="value" :error-messages="errors"></v-select>
            </validation-provider>
            <validation-provider v-slot="{errors}" name="condicion de riesgo" rules="required">
              <v-select v-model="pa.condicion_riesgo" label="¿El paciente cumple con alguna condición de riesgo?"
                        :items="condicionesRiesgo" item-value="value" item-text="text" :error-messages="errors"></v-select>
            </validation-provider>
            <v-text-field v-if="pa.condicion_riesgo === 11" v-model="pa.condicion_riesgo_otro" label="Especifique condición de riesgo"></v-text-field>
            <validation-provider v-slot="{errors}" name="marca" rules="required">
              <v-select v-model="pa.marca_prueba" label="Marca prueba" :error-messages="errors"
                        :items="marcas" item-text="text" item-value="value"></v-select>
            </validation-provider>
            <validation-provider v-slot="{errors}" name="tipo muestra" rules="required">
              <v-select v-model="pa.tipo_muestra" label="Tipo muestra" :error-messages="errors"
                        :items="tipoMuestras" item-text="text" item-value="value"></v-select>
            </validation-provider>
            <validation-provider v-slot="{errors}" name="tipo lectura" rules="required">
              <v-select v-model="pa.tipo_lectura" label="Tipo lectura" :error-messages="errors"
                        :items="tipoLecturas" item-text="text" item-value="value"></v-select>
            </validation-provider>
            <validation-provider v-slot="{errors}" name="resultado" rules="required">
              <v-radio-group v-model="pa.resultado" :error-messages="errors" label="INGRESE RESULTADO">
                <v-radio label="NO REACTIVO" :value="0"></v-radio>
                <v-radio label="REACTIVO" :value="1"></v-radio>
                <v-radio label="INVÁLIDO" :value="2"></v-radio>
                <v-radio label="INDETERMINADO" :value="3"></v-radio>
              </v-radio-group>
            </validation-provider>
            <v-text-field v-model="pa.observaciones" label="¿Desea añadir alguna observación?"></v-text-field>
          </v-card-text>
          <v-divider></v-divider>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="normal" text @click="close">CANCELAR</v-btn>
            <v-btn color="primary" @click="save">GUARDAR</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </validation-observer>
  </div>
</template>

<script>
import {mapState, mapMutations, mapActions} from 'vuex'

export default {
  props: ['pa'],
  data() {
    return {
      clasificaciones: [
        { value: 1, text: 'ASINTOMATICO' },
        { value: 2, text: 'LEVE' },
        { value: 3, text: 'MODERADA' },
        { value: 4, text: 'SEVERA' },
      ],
      condicionesRiesgo: [
        { value: 1, text: 'HIPERTENSION ARTERIAL' },
        { value: 2, text: 'ENFERMEDAD CARDIOVASCULAR' },
        { value: 3, text: 'DIABETES' },
        { value: 4, text: 'OBESIDAD' },
        { value: 5, text: 'ENFERMEDAD PULMONAR CRONICA' },
        { value: 6, text: 'INSUFICIENCIA RENAL CRONICA' },
        { value: 7, text: 'ENFERMEDAD O TRATAMIENTO INMUNOSUPRESOR' },
        { value: 8, text: 'CANCER' },
        { value: 9, text: 'PERSONAL DE SALUD' },
        { value: 10, text: 'NINGUNA CONDICION DE RIESGO' },
        { value: 11, text: 'OTRA CONDICION DE RIESGO' },
      ],
      marcas: [
        {value: 1, text: "CLINITEST"},
      ],
      tipoMuestras: [
        {value: 1, text: "SALIVA"},
        {value: 2, text: "HISOPADO NASAL"},
        {value: 3, text: "HISOPADO NASOFARINGEO"},
        {value: 4, text: "HISOPA OROFARINGEO"},
      ],
      tipoLecturas: [
        {value: 1, text: "VISUAL"},
        {value: 2, text: "LECTOR DIGITAL"},
      ]
    }
  },
  computed: {
    ...mapState('pa',[
      'dialog',
    ]),
  },
  methods: {
    ...mapMutations('pa',[
      'SHOW_SAVE_PRUEBA_ANTIGENA_DIALOG',
    ]),
    ...mapActions('pa',[
      'update'
    ]),
    close() {
      this.SHOW_SAVE_PRUEBA_ANTIGENA_DIALOG(false)
      this.$refs.observer.reset()
    },
    save() {
      this.$refs.observer.validate().then(async valid => {
        if (!valid) {
          return
        }
        await this.update(this.pa)
        this.$refs.observer.reset()
      })
    }
  }
}
</script>
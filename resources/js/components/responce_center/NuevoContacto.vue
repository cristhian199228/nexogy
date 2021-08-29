<template>
  <div>
    <v-dialog fullscreen v-model="dialog.nuevo_contacto" persistent>
      <v-card outlined>
        <v-toolbar dark color="primary">
          <v-btn icon dark @click="close">
            <v-icon>mdi-close</v-icon>
          </v-btn>
          <v-toolbar-title>INGRESAR CONTACTO DIRECTO</v-toolbar-title>
          <v-spacer></v-spacer>
        </v-toolbar>
        <v-divider></v-divider>
        <v-stepper v-model="e1" >
          <v-stepper-header>
            <v-stepper-step :complete="e1 > 1" step="1">AVISO</v-stepper-step>
            <v-divider></v-divider>
            <v-stepper-step :complete="e1 > 2" step="2">INGRESO DE CONTACTO DIRECTO</v-stepper-step>
            <v-divider></v-divider>
          </v-stepper-header>
          <v-col cols="12" offset-lg="2" offset-md="2" lg="8" md="8" sm="12" xs="12">
            <v-stepper-items>
              <v-stepper-content class="ma-0 px-0" step="1">
                <v-card outlined>
                  <v-card-title>CONTACTO DIRECTO</v-card-title>
                  <v-card-text>
                    <p>Persona que haya tenido contacto:</p>
                    <ul>
                      <li>Entre dos días antes y catorce días después del inicio de los síntomas con un caso probable o confirmado de COVID 19, o</li>
                      <li>entre dos días antes y catorce días después de la fecha de obtención de la muestra en la que se basó la confirmación de COVID 19</li>
                    </ul>
                    <p>Y que además estuvo expuesta a alguna de las situaciones siguientes:</p>
                    <ul>
                      <li>Contacto Personal (sin utilizar Máscara de Uso Comunitario) con un caso probable o confirmado a menos de
                        un metro de distancia y durante más de 15 minutos.</li>
                      <li>Contacto Físico Directo con un caso probable o confirmado.</li>
                      <li>Atención Directa a un paciente con COVID-19 probable o confirmada sin utilizar el Equipo de Protección Personal
                        recomendado (Respirador N95/FFP2, Careta Facial, Protección Ocular, Guantes de Protección Biológica, Traje de
                        Protección Biológica y Botas para Protección Biológica).
                      </li>
                      <li>Situaciones de otro tipo en función de la evaluación específica del riesgo:
                        <ul>
                          <li>Compartir espacios cerrados (con limitada ventilación) por 2 o más horas, sin uso de Máscara de Uso Comunitario.</li>
                          <li>Compartir dormitorio o lugar de descanso en camas contiguas horizontal y verticalmente con menos de un
                            metro de distancia de separación entre los bordes.
                          </li>
                          <li>Compartir comedores a menos de un metro de distancia sin una separación física de más de 60 centímetros desde
                            el tablero (barreras físicas que se encuentren instaladas sobre la superficie de la mesa y tengan una
                            altura no menor de 60 centímetros)
                          </li>
                          <li>Compartir equipos, herramientas u otros sin haber realizado una previa limpieza de estos entre usuarios.
                          </li>
                        </ul>
                      </li>
                    </ul>
                  </v-card-text>
                  <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn text @click="close">CANCELAR</v-btn>
                    <v-btn color="primary" @click="e1 = 2">SIGUIENTE</v-btn>
                  </v-card-actions>
                </v-card>
              </v-stepper-content>
              <v-stepper-content class="ma-0 px-0" step="2">
                <v-card outlined>
                  <v-card-title>DATOS DEL CONTACTO DIRECTO</v-card-title>
                  <v-divider></v-divider>
                  <v-card-text>
                    <validation-observer ref="observer" v-slot="{validate}">
                      <validation-provider v-slot="{errors}" name="nombre completo" rules="required|min:10">
                        <v-text-field dense outlined label="NOMBRE COMPLETO" :error-messages="errors" v-model="form.nombre_completo"></v-text-field>
                      </validation-provider>
                      <validation-provider v-slot="{errors}" name="celular" rules="min:9|integer">
                        <v-text-field counter dense outlined label="CELULAR" :error-messages="errors" v-model.trim="form.celular" ></v-text-field>
                      </validation-provider>
                      <v-text-field dense outlined label="CARGO" v-model="form.cargo"></v-text-field>
                      <v-textarea clearable dense outlined label="DETALLE" v-model="form.detalle"></v-textarea>
                    </validation-observer>
                  </v-card-text>
                  <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn text @click="e1 = 1">ATRAS</v-btn>
                    <v-btn color="primary" @click="store">GUARDAR</v-btn>
                  </v-card-actions>
                </v-card>
              </v-stepper-content>
            </v-stepper-items>
          </v-col>
        </v-stepper>
      </v-card>
    </v-dialog>
  </div>
</template>


<script>
import { mapState } from 'vuex';
import {required,integer, min } from "vee-validate/dist/rules";
import { extend, ValidationObserver, ValidationProvider, setInteractionMode } from 'vee-validate'

setInteractionMode("eager");
extend("required", {
  ...required,
  message: "{_field_} can not be empty"
});
extend("min", {
  ...min,
  message: "{_field_} may not be less than {length} characters"
});

extend("integer", {
  ...integer,
  message: "{_field_} must be integer"
});

export default {
  components: {
    ValidationProvider,
    ValidationObserver,
  },
  data() {
    return {
      form: {
        nombre_completo: null,
        celular: null,
        cargo: null,
        detalle: null,
      },
      e1: 1,
    }
  },
  methods: {
    store () {
      this.$refs.observer.validate().then(async isValid => {
        if (isValid) {
          await this.$store.dispatch('rc/storeCD', this.form);
          this.clean();
          this.$refs.observer.reset();
          this.e1 = 1;
        }
      });
    },
    clean() {
      for (const key of Object.keys(this.form)) {
        this.form[key] = null;
      }
    },
    close () {
      this.$store.commit('rc/SHOW_DIALOG_CONTACTO', false);
      this.$refs.observer.reset();
      this.clean();
      this.e1 = 1;
    },
  },
  computed: {
    ...mapState('rc',['dialog']),
  }
}
</script>

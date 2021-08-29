<template>
  <div>
    <validation-observer ref="observer" v-slot="{validate}">
      <v-dialog persistent scrollable v-model="dialog.ps.update" max-width="600px">
        <v-card>
          <v-card-title>GUARDAR PS</v-card-title>
          <v-divider></v-divider>
          <v-card-text style="height: 700px;">
            <h5>PROCEDENCIA DE SOLICITUD</h5>
            <v-switch v-model="ps.ps_llamada_113" hide-details label="Llamada al 113"></v-switch>
            <v-switch v-model="ps.ps_de_eess" hide-details label="De EESS"></v-switch>
            <v-switch v-model="ps.ps_contactocasocon" hide-details label="Contacto con caso confirmado"></v-switch>
            <v-switch v-model="ps.ps_contactocasosos" hide-details label="Contacto con caso sospechoso"></v-switch>
            <v-switch v-model="ps.ps_personaext" hide-details label="Personal extranjero (migraciones)"></v-switch>
            <v-switch v-model="ps.ps_personalsalud" label="Personal de salud"></v-switch>
            <v-text-field dense outlined v-model="ps.ps_otro" label="Otro"></v-text-field>
            <v-divider></v-divider>
            <h5>RESULTADO DE LA PRUEBA</h5>
            <v-switch v-model="ps.p1_react1gm" @change="reactivoIgm" hide-details label="REACTIVO IgM"></v-switch>
            <v-switch v-model="ps.p1_reactigg" @change="reactivoIgg" hide-details label="REACTIVO IgG"></v-switch>
            <v-switch v-model="ps.p1_reactigm_igg" @change="reactivoIgmIgg" hide-details label="REACTIVO IgM/IgG"></v-switch>
            <v-switch v-model="ps.no_reactivo" @change="noReactivo" hide-details label="NO REACTIVO"></v-switch>
            <v-switch v-model="ps.p1_positivo_recuperado" @change="positivoRecuperado" hide-details label="POSITIVO RECUPERADO"></v-switch>
            <v-switch v-model="ps.p1_positivo_vacunado" @change="positivoVacunado" hide-details label="POSITIVO VACUNADO"></v-switch>
            <v-switch v-model="ps.p1_positivo_persistente" @change="positivoPersistente"  hide-details label="POSITIVO PERSISTENTE"></v-switch>
            <v-switch v-model="ps.invalido" label="INVALIDO" @change="esInvalido"></v-switch>
            <validation-provider v-slot="{errors}" name="marca" rules="required">
              <v-select
                  v-model="ps.p1_marca"
                  :items="marca_opt"
                  item-text="name"
                  item-value="id"
                  label="Marca PS"
                  outlined
                  dense
                  :error-messages="errors"
              ></v-select>
            </validation-provider>
<!--            <v-divider></v-divider>
            <h5>ANTECEDENTES</h5>
            <validation-provider v-slot="{errors}" vid="positivo_anterior" name="pregunta">
              <v-switch v-model="ps.positivo_anterior" @change="positivoAnterior" hide-details label="¿El paciente salío positivo anteriormente?"></v-switch>
            </validation-provider>
            <template v-if="ps.positivo_anterior">
              <validation-provider v-slot="{errors}" rules="required_if:positivo_anterior,true" name="lugar">
                <v-text-field v-model="ps.lugar_positivo_anterior" label="Lugar donde se tomó la prueba" :error-messages="errors"
                              prepend-icon="mdi-map-marker"></v-text-field>
              </validation-provider>
              <v-menu v-model="menu6" :close-on-content-click="false" :nudge-right="40" transition="scale-transition" offset-y min-width="auto">
                <template v-slot:activator="{ on, attrs }">
                  <validation-provider v-slot="{errors}" rules="required_if:positivo_anterior,true" name="fecha">
                    <v-text-field v-model="ps.fecha_positivo_anterior" label="En que año/mes salió positivo?" :error-messages="errors"
                                  prepend-icon="mdi-calendar" readonly v-bind="attrs" v-on="on"></v-text-field>
                  </validation-provider>
                </template>
                <v-date-picker type="month" v-model="ps.fecha_positivo_anterior" locale="es"
                               @input="menu6 = false" min="2020-01" :max="new Date().toISOString().substr(0,7)"></v-date-picker>
              </v-menu>
            </template>-->
            <v-divider></v-divider>
            <h5>CLASIFICACION CLÍNICA DE LA SEVERIDAD</h5>
            <v-switch v-model="ps.ccs" @change="ccsChange" label="¿El paciente presenta alguna condición de riesgo?"></v-switch>
            <template v-if="ps.ccs">
              <v-select
                  v-model="ps.condicion_riesgo"
                  :items="condicion_riesgo_opt"
                  item-text="name"
                  item-value="id"
                  label="CLASIFICACIÓN DE RIESGO"
                  outlined
                  dense
              ></v-select>
              <v-text-field dense outlined v-model="ps.condicion_riesgo_detalle" label="Escriba aquí los detalles" class="mt-2"></v-text-field>
            </template>
          </v-card-text>
          <v-divider></v-divider>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn text color="normal" @click="close">ATRAS</v-btn>
            <v-btn color="primary" @click="guardarPS">GUARDAR</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </validation-observer>
  </div>
</template>

<script>
import {mapState, mapMutations, mapActions} from 'vuex';

export default {
  props: ['ps'],
  data() {
    return {
      marca_opt: [
        {id: 1, name: "HIGH TOP"},
        {id: 2, name: "MONTEST"},
        {id: 3, name: "LABNOVATION"},
        {id: 4, name: "ZYBIO"},
        {id: 5, name: "BIOSENSOR"},
        {id: 6, name: "CELLEX"},
      ],
      condicion_riesgo_opt: [
        {id: 1, name: "LEVE"},
        {id: 2, name: "MODERADO"},
        {id: 3, name: "SEVERO"},
      ],
      otros: false,
      menu6: false,
    }
  },
  computed: {
    ...mapState('controlador',['dialog']),
  },
  methods: {
    ...mapActions('controlador',['updatePS']),
    ...mapMutations('controlador',['SHOW_DIALOG_UPDATE_PS']),
    guardarPS() {
      this.$refs.observer.validate().then(valid => {
        if (valid) {
          this.updatePS(this.ps);
        }
      })
    },
    close() {
      this.SHOW_DIALOG_UPDATE_PS(false);
      this.$refs.observer.reset();
    },
    esInvalido(val){
      if(val) {
        this.ps.no_reactivo = false
        this.ps.p1_reactigm_igg = false
        this.ps.p1_reactigg = false
        this.ps.p1_react1gm = false
        this.ps.p1_positivo_recuperado = false
        this.ps.p1_positivo_persistente = false
        this.ps.p1_positivo_vacunado = false
      }
    },
    reactivoIgm(val) {
      if(val){
        this.ps.invalido = false
        this.ps.p1_reactigg = false
        this.ps.p1_reactigm_igg = false
        this.ps.no_reactivo = false
        this.ps.p1_positivo_recuperado = false
        this.ps.p1_positivo_vacunado = false
      }
    },
    reactivoIgg(val) {
      if(val){
        this.ps.invalido = false
        this.ps.p1_react1gm = false
        this.ps.p1_reactigm_igg = false
        this.ps.no_reactivo = false
        this.ps.p1_positivo_persistente = false
      }
    },
    reactivoIgmIgg(val){
      if(val){
        this.ps.invalido = false
        this.ps.p1_react1gm = false
        this.ps.p1_reactigg = false
        this.ps.no_reactivo = false
        this.ps.p1_positivo_recuperado = false
        this.ps.p1_positivo_vacunado = false
      }
    },
    noReactivo(val){
      if(val){
        this.ps.invalido = false
        this.ps.p1_react1gm = false
        this.ps.p1_reactigg = false
        this.ps.p1_reactigm_igg = false
        this.ps.p1_positivo_recuperado = false
        this.ps.p1_positivo_persistente = false
        this.ps.p1_positivo_vacunado = false
      }
    },
    positivoRecuperado(val) {
      if(val) {
        this.ps.invalido = false
        this.ps.p1_react1gm = false
        this.ps.p1_reactigm_igg = false
        this.ps.no_reactivo = false
        this.ps.p1_reactigg = true
        this.ps.p1_positivo_persistente = false
        this.ps.p1_positivo_vacunado = false
      }
    },
    positivoPersistente(val){
      if(val) {
        this.ps.invalido = false
        this.ps.no_reactivo = false
        this.ps.p1_reactigg = false
        this.ps.p1_positivo_recuperado = false
        this.ps.p1_positivo_vacunado = false
      }
    },
    positivoAnterior (val) {
      if (!val) {
        this.ps.fecha_positivo_anterior = null;
        this.ps.lugar_positivo_anterior = null;
      }
    },
    ccsChange (val) {
      if (!val) {
        this.ps.condicion_riesgo = null;
        this.ps.condicion_riesgo_detalle = null;
      }
    },
    positivoVacunado(val){
      if(val) {
        this.ps.invalido = false
        this.ps.p1_react1gm = false
        this.ps.p1_reactigm_igg = false
        this.ps.no_reactivo = false
        this.ps.p1_reactigg = true
        this.ps.p1_positivo_persistente = false
        this.ps.p1_positivo_recuperado = false
      }
    }
  }
}
</script>

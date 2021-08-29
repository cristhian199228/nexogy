<template>
  <div>
    <v-card outlined>
      <v-card-title>INTELIGENCIA SANITARIA</v-card-title>
      <v-data-table :headers="headers" :items="fichas" hide-default-footer :items-per-page="15">
        <template v-slot:header.paciente_isos.full_name="{header}">
          <v-text-field dense hide-details v-model="buscar" label="Paciente" append-icon="mdi-magnify" @click:append="getFichas(1)"
                        @keyup.enter="getFichas(1)" clearable @click:clear="clearBuscarField" single-line></v-text-field>
        </template>
        <template v-slot:header.fecha="{header}">
          <filtro-fecha :module="modulo" />
        </template>
        <template v-slot:header.estacion.sede.descripcion="{header}">
          <filtro-sede :module="modulo" />
        </template>
        <template v-slot:header.prs="{header}">
          <filtro-resultado-prs :module="modulo" />
        </template>
        <template v-slot:header.pcr="{header}">
          <filtro-resultado-pcr :module="modulo" />
        </template>

        <template v-slot:item.dc="{item}">
          <template v-if="item.datos_clinicos.length > 0">
            <v-chip dark small color="red darken-2">SI</v-chip>
          </template>
          <template v-else>
            <v-chip dark small color="green darken-2">NO</v-chip>
          </template>
        </template>

        <template v-slot:item.ae="{item}">
          <template v-if="item.antecedentes_ep.length > 0">
            <v-chip dark small color="red darken-2">SI</v-chip>
          </template>
          <template v-else>
            <v-chip dark small color="green darken-2">NO</v-chip>
          </template>
        </template>

        <template v-slot:item.prs="{item}">
          <v-chip v-if="item.prueba_serologica.length > 0" dark small :color="getPrsColorResult(item.prueba_serologica[0].resultado)">
            {{ getPrsStrResult(item.prueba_serologica[0].resultado) }}</v-chip>
        </template>

        <template v-slot:item.pcr="{item}">
          <v-chip v-if="item.pcr_prueba_molecular" dark small :color="getPcrColorResult(item.pcr_prueba_molecular.resultado)">
            {{ getPcrStrResult(item.pcr_prueba_molecular.resultado) }}</v-chip>
        </template>

        <template v-slot:item.curva="{item}">
          <template v-if="item.pcr_prueba_molecular && item.pcr_prueba_molecular.pcr_foto_muestra">
            <v-btn @click="verCurva(item.pcr_prueba_molecular.pcr_foto_muestra)" small icon><v-icon small>mdi-eye</v-icon></v-btn>
          </template>
        </template>

        <template v-slot:item.dias_bloqueo_pcr="{item}">
          <template v-if="item.pcr_prueba_molecular">
            <v-edit-dialog
              large
              persistent
              @open="form.id = item.pcr_prueba_molecular.idpcr_pruebas_moleculares"
              @save="guardarReevaluacionPcr(form)"
              @cancel="cancel"
            >
              {{ item.pcr_prueba_molecular.reevaluacion.length > 0 ?
              item.pcr_prueba_molecular.reevaluacion[0].dias_bloqueo :
              item.pcr_prueba_molecular.resultado ? 13 : 0  }}
              <template v-slot:input>
                <div class="mt-4 text-h6">
                  Dias bloqueo PCR
                </div>
                <v-text-field
                  v-model="form.dias_bloqueo"
                  label="Ingrese dias"
                  single-line
                  autofocus
                  type="number"
                ></v-text-field>
              </template>
            </v-edit-dialog>
          </template>
        </template>

        <template v-slot:item.dias_bloqueo_prs="{item}">
          <template v-if="item.prueba_serologica.length > 0">
            <v-edit-dialog
              large
              persistent
              @open="form.id = item.prueba_serologica[0].idpruebaserologicas"
              @save="guardarReevaluacionPrs(form)"
              @cancel="cancel"
            >
              {{ item.prueba_serologica[0].reevaluacion.length > 0 ?
              item.prueba_serologica[0].reevaluacion[0].dias_bloqueo :
              item.prueba_serologica[0].dias_bloqueo }}
              <template v-slot:input>
                <div class="mt-4 text-h6">
                  Dias bloqueo PRS
                </div>
                <v-text-field
                  v-model="form.dias_bloqueo"
                  label="Ingrese dias"
                  single-line
                  autofocus
                  type="number"
                ></v-text-field>
              </template>
            </v-edit-dialog>
          </template>
        </template>

        <template v-slot:item.acciones="{item}">
          <v-btn @click="mostrarXml(item.idficha_paciente)" small icon><v-icon small>mdi-xml</v-icon></v-btn>
          <v-btn @click="enviarCorreo(item.idficha_paciente)" small icon><v-icon small>mdi-email</v-icon></v-btn>
        </template>

      </v-data-table>
      <DialogVerCurva :curva="curva" />
      <DialogEnviarCorreo :idficha_paciente="idficha" />
      <paginate :store="modulo" />
    </v-card>
  </div>
</template>

<script>
import {mapGetters, mapActions} from 'vuex'
import DialogVerCurva from "./DialogVerCurva";
import DialogEnviarCorreo from "./DialogEnviarCorreo";
import paginate from "../paginate";
import moment from 'moment'
import FiltroFecha from "../headers/FiltroFecha";
import FiltroResultadoPcr from "../headers/FiltroResultadoPcr";
import FiltroResultadoPrs from "../headers/FiltroResultadoPrs";
import FiltroSede from "../headers/FiltroSede";

export default {
  components: {
    DialogVerCurva,
    DialogEnviarCorreo,
    paginate,
    FiltroFecha,
    FiltroResultadoPcr,
    FiltroResultadoPrs,
    FiltroSede,
  },
  data() {
    return {
      modulo: 'is',
      menu: false,
      headers: [
        {text: 'N°', value: 'contador', align: 'start', sortable: false},
        {text: 'Fecha', value: 'fecha', sortable: false},
        {text: 'Sede', value: 'estacion.sede.descripcion', sortable: false},
        {text: 'Paciente', value: 'paciente_isos.full_name', sortable: false},
        {text: 'AE', value: 'ae', sortable: false},
        {text: 'DC', value: 'dc', sortable: false},
        {text: 'PRS', value: 'prs', sortable: false},
        {text: 'PRS Dias Bloq.', value: 'dias_bloqueo_prs', sortable: false},
        {text: 'PCR', value: 'pcr', sortable: false},
        {text: 'PCR Dias Bloq.', value: 'dias_bloqueo_pcr', sortable: false},
        {text: 'Curva', value: 'curva', sortable: false},
        {text: 'Acciones', value: 'acciones', sortable: false},
      ],
      dates: [
        moment().format('YYYY-MM-DD'),
        moment().format('YYYY-MM-DD')
      ],
      curva: {},
      form: {
        id: null,
        dias_bloqueo: null,
      },
      idficha: null,
    }
  },
  methods: {
    ...mapActions('is',[
      'getFichas',
      'guardarReevaluacionPrs',
      'guardarReevaluacionPcr',
    ]),
    cancel() {

    },
    mostrarXml(idficha_paciente) {
      window.open(`api/v1/is/xml/${idficha_paciente}`,"_blank");
    },
    enviarCorreo(idficha_paciente) {
      const r = confirm('Desea enviar el xml por correo?')
      if (r) this.$store.dispatch('is/sendMail', idficha_paciente)
      //this.idficha = idficha_paciente
      //this.$store.commit('is/SHOW_DIALOG_ENVIAR_CORREO', true)
    },
    clearFecha() {
      this.$store.commit('is/SET_FILTRO_FECHA', null)
      this.getFichas(1)
    },
    guardarFechas(){
      this.$refs.menu.save(this.dates)
      this.$store.commit('is/SET_FILTRO_FECHA', this.dates)
      this.getFichas(1)
    },
    clearBuscarField() {
      this.$store.commit('is/SET_CRITERIO_BUSQUEDA', null)
      this.getFichas(1)
    },
    verCurva(curva) {
      this.curva = Object.assign({}, curva)
      this.$store.commit('is/SHOW_DIALOG_CURVA', true)
    },
  },
  computed: {
    ...mapGetters('is',['fichas','currentPage']),
    ...mapGetters([
      'getPcrStrResult',
      'getPcrColorResult',
      'getPrsStrResult',
      'getPrsColorResult'
    ]),
    dateStr() {
      return this.dates.join(' ~ ')
    },
    buscar: {
      get() {
        return this.$store.getters["is/filtroBuscar"]
      },
      set(val) {
        this.$store.commit('is/SET_CRITERIO_BUSQUEDA', val)
      }
    },
  },
  created() {
    this.getFichas(this.currentPage)
  }
}
</script>
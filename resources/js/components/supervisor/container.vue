<template>
  <div>
    <v-card outlined>
      <buscador :store="modulo" title="SUPERVISOR PRS" />
      <v-data-table :headers="headers" :items="fichas" :loading="loading_table" dense disable-pagination disable-sort hide-default-footer>
        <template v-slot:header.wp="{header}">
          <v-select class="font-weight-medium" hide-details v-model="filtro.enviado_wp" label="Whatsapp" :items="wps"
                    item-value="value" item-text="text" clearable @change="changeEnviadoWp"></v-select>
        </template>
        <template v-slot:header.est="{header}">
          <v-select class="font-weight-medium" hide-details v-model="filtro.estacion" label="Estacion" :items="getEstacionesPorSede"
                    item-value="idestaciones" item-text="nom_estacion" clearable @change="changeEstacion"></v-select>
        </template>
        <template v-slot:header.nom_empresa="{header}">
          <v-text-field
            v-model="buscar_empresa"
            append-icon="mdi-magnify"
            label="Empresa"
            @click:append="getFichas"
            @keyup.enter="getFichas"
            clearable
            hide-details
            single-line
            @click:clear="clearBuscarEmpresa"
          ></v-text-field>
        </template>
        <template v-slot:header.nom_completo="{header}">
          <v-text-field
            v-model="buscar_paciente"
            append-icon="mdi-magnify"
            label="Paciente"
            @click:append="getFichas"
            @keyup.enter="getFichas"
            clearable
            hide-details
            single-line
            @click:clear="clearBuscarPaciente"
          ></v-text-field>
        </template>
        <template v-slot:header.temp="{header}">
          <v-select class="font-weight-medium" hide-details v-model="filtro.temperatura" label="Temp." :items="temperaturas"
                    item-value="value" item-text="text" clearable @change="changeTemperatura"></v-select>
        </template>
        <template v-slot:header.fin="{header}">
          <v-select class="font-weight-medium" hide-details v-model="filtro.estado" label="Estado" :items="estados"
                    item-value="value" item-text="text" clearable @change="changeEstado"></v-select>
        </template>
        <template v-slot:header.prs="{header}">
          <v-select class="font-weight-medium" hide-details v-model="filtro.ps" label="Resultado" :items="resultados"
                    item-value="value" item-text="text" clearable @change="changeResultado"></v-select>
        </template>
        <template v-slot:item.contador="{item}">
          <small :class="colorFinalizado(item.estado)">{{ item.contador }}</small>
        </template>
        <template v-slot:item.nom_completo="{item}">
          <small :class="colorFinalizado(item.estado)">
            {{ item.paciente_isos.full_name }}
          </small>
        </template>
        <template v-slot:item.celular="{item}">
          <v-edit-dialog
            large
            persistent
            @open="form.id_paciente = item.paciente_isos.idpacientes; form.celular = item.paciente_isos.celular"
            @save="updateCelular()"
          >
            <small :class="colorFinalizado(item.estado)">
              {{ item.paciente_isos.celular }}
            </small>
            <template v-slot:input>
              <div class="mt-4 text-h6">
                ACTUALIZAR CELULAR
              </div>
              <v-text-field
                v-model="form.celular"
                label="Ingrese dias"
                single-line
                autofocus
                counter
                type="number"
              ></v-text-field>
            </template>
          </v-edit-dialog>
        </template>
        <template v-slot:item.nom_empresa="{item}">
          <small :class="colorFinalizado(item.estado)">
            {{ item.paciente_isos.empresa.descripcion }}
          </small>
        </template>
        <template v-slot:item.est="{item}">
          <small :class="colorFinalizado(item.estado)" v-if="item.estacion && item.estacion.sede">
            {{ item.estacion.nom_estacion }}
          </small>
        </template>
        <template v-slot:item.temp="{item}">
          <template v-if="item.temperatura && item.temperatura.length > 0">
            <v-chip v-if="item.temperatura[0].valortemp > 37.8" dark small color="red darken-2">
              {{ item.temperatura[0].valortemp }}
            </v-chip>
            <v-chip v-else dark small color="green darken-2">
              {{ item.temperatura[0].valortemp }}
            </v-chip>
          </template>
        </template>
        <template v-slot:item.prs="{item}">
          <template v-if="item.prueba_serologica.length > 0 && item.prueba_serologica[0].resultado">
            <v-chip small dark :color="getColor(item.prueba_serologica[0].resultado)">
              {{ getResult(item.prueba_serologica[0].resultado) }}
            </v-chip>
          </template>
        </template>
        <template v-slot:item.wp="{item}">
          <template v-if="item.prueba_serologica.length > 0 && item.prueba_serologica[0].resultado">
            <v-btn small icon :color="item.prueba_serologica[0].envio_w_p_count > 0 ? 'green darken-2' : ''"
                   :disabled="!item.enviar_mensaje"
                   @click="enviarWhatsapp(item.prueba_serologica[0])"><v-icon small>mdi-whatsapp</v-icon></v-btn>
          </template>
        </template>
        <template v-slot:item.acciones="{item}">
          <v-icon @click="accionesWS(item)"
                  :color="item.datos_clinicos.length > 0 || item.antecedentes_ep.length > 0 ||
                        (item.prueba_serologica.length > 0 && item.prueba_serologica[0].resultado !== 1 && item.prueba_serologica[0].resultado !== 2 &&
                        item.prueba_serologica[0].resultado !== 3) ?
                        'red darken-2' : ''" small>mdi-eye</v-icon>
        </template>
        <template v-slot:item.fin="{item}">
          <v-switch dense hide-details v-model="item.estado" color="primary" :disabled="item.estado === 1"
                    @change="finalizarAtencion(item.idficha_paciente)"></v-switch>
        </template>
      </v-data-table>
      <update-fab-button :store="modulo" />
    </v-card>
    <SelectSede :modulo="modulo" />
    <DialogPaciente :ficha="ficha" />
    <DialogMediweb />
    <paginate :store="modulo" />
  </div>
</template>

<script>
import { mapState, mapGetters, mapActions} from 'vuex'
import SelectSede from "../SelectSede";
import buscador from "../buscador";
import paginate from "../paginate";
import DialogPaciente from "./DialogPaciente";
import DialogMediweb from "./DialogMediweb";
import UpdateFabButton from "../UpdateFabButton";

export default {
  components: {
    SelectSede,
    buscador,
    paginate,
    DialogPaciente,
    DialogMediweb,
    UpdateFabButton,
  },
  data() {
    return {
      modulo: "super_prs",
      ficha: {},
      headers: [
        {text: "N°", align: "start", value: "contador", sortable: false},
        {text: "Nombres", value: "nom_completo", sortable: false},
        {text: "Celular", value: "celular", sortable: false},
        {text: "Empresa", value: "nom_empresa", sortable: true},
        {text: "Estacion", value: "est", sortable: true},
        {text: "T°", value: "temp", sortable: false},
        {text: "PS", value: "prs", sortable: false},
        {text: "WP", value: "wp", sortable: false},
        {text: "A", value: "acciones", sortable: false},
        {text: "Fin.", value: "fin", sortable: false},
      ],
      wps: [
        {value: 0, text: 'NO ENVIADO'},
        {value: 1, text: 'ENVIADO'},
        {value: 2, text: 'NO ACEPTÓ'},
      ],
      temperaturas: [
        {value: 0, text: 'SIN FIEBRE'},
        {value: 1, text: 'CON FIEBRE'},
      ],
      estados: [
        {value: 0, text: 'NO FIN.'},
        {value: 1, text: 'FIN.'},
      ],
      resultados: [
        {value: 1, text: 'NEG'},
        {value: 2, text: 'IGG R'},
        {value: 3, text: 'IGG V'},
        {value: 4, text: 'IGG'},
        {value: 5, text: 'IGM'},
        {value: 6, text: 'IGM/IGG'},
        {value: 7, text: 'IGM P'},
        {value: 8, text: 'IGM/IGG P'},
      ],
      form: {
        id_paciente: null,
        celular: null,
      }
    }
  },
  methods: {
    ...mapActions('super_prs',['getFichas']),
    updateCelular() {
      this.$store.dispatch('super_prs/updateCelular', this.form)
    },
    accionesWS(ficha) {
      this.ficha = ficha;
      this.$store.commit('super_prs/SET_ID_FICHA', ficha.idficha_paciente);
      this.$store.dispatch('super_prs/getPrsPasadas', ficha.paciente_isos.idpacientes);
      this.$store.commit('super_prs/SHOW_DIALOG_PACIENTE', true);
    },
    finalizarAtencion(id_ficha) {
      this.$store.dispatch('super_prs/finalizarAtencion', id_ficha)
    },
    colorFinalizado(estado) {
      let color = "";
      if (estado === 1) color = "blue--text text--darken-2 font-weight-black";
      else color = "black--text"
      return color;
    },
    enviarWhatsapp(prueba) {
      let r = confirm("Esta seguro que desea enviar el whatsapp?");
      if(r) this.$store.dispatch('super_prs/storeWP', prueba);
    },
    getResult(resultado) {
      let res = "";
      switch (resultado) {
        case 1: res = "NEG"; break;
        case 2: res = "IGG R"; break;
        case 3: res = "IGG V"; break;
        case 4: res = "IGG"; break;
        case 5: res = "IGM"; break;
        case 6: res = "IGM/IGG"; break;
        case 7: res = "IGM P"; break;
        case 8: res = "IGM/IGG P"; break;
      }
      return res;
    },
    getColor(resultado) {
      let res = "";
      switch (resultado) {
        case 1: res = "green darken-2"; break;
        case 2:
        case 3: res = "orange darken-2"; break;
        case 4:
        case 5:
        case 6:
        case 7:
        case 8: res = "red darken-2"; break;
      }
      return res;
    },
    changeEnviadoWp(val) {
      this.$store.commit('super_prs/SET_FILTRO_MENSAJE_WHATSAPP', val)
      this.$store.dispatch('super_prs/getFichas')
    },
    changeEstacion(val) {
      this.$store.commit('super_prs/SET_FILTRO_ESTACION', val)
      this.$store.dispatch('super_prs/getFichas')
    },
    clearBuscarEmpresa() {
      this.$store.commit('super_prs/SET_FILTRO_EMPRESA', null)
      this.getFichas()
    },
    clearBuscarPaciente() {
      this.$store.commit('super_prs/SET_CRITERIO_BUSQUEDA', null)
      this.getFichas()
    },
    changeEstado(val) {
      this.$store.commit('super_prs/SET_FILTRO_ESTADO', val)
      this.getFichas()
    },
    changeTemperatura(val) {
      this.$store.commit('super_prs/SET_FILTRO_TEMPERATURA', val)
      this.getFichas()
    },
    changeResultado(val) {
      this.$store.commit('super_prs/SET_FILTRO_PS', val)
      this.getFichas()
    },
  },
  computed: {
    ...mapState(['id_sede']),
    ...mapState('super_prs',['loading','loading_table','filtro']),
    ...mapGetters(['getEstacionesPorSede']),
    fichas: {
      get() {
        return this.$store.state.super_prs.data;
      }
    },
    buscar_empresa: {
      get() {
        return this.$store.state.super_prs.filtro.empresa;
      },
      set (val) {
        this.$store.commit('super_prs/SET_FILTRO_EMPRESA', val)
      }
    },
    buscar_paciente: {
      get() {
        return this.$store.state.super_prs.buscar
      },
      set (val) {
        this.$store.commit('super_prs/SET_CRITERIO_BUSQUEDA', val)
      }
    }
  },
  created() {
    if(!this.id_sede) this.$store.commit('SHOW_DIALOG_SEDE', true);
    this.$store.dispatch('getEstaciones')
    this.getFichas()
  },
}
</script>

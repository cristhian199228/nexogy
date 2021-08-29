<template>
  <div>
    <buscador-pcr-salud :store="modulo" title="REGISTRO DE PACIENTES" />
    <v-row v-if="pacientes && pacientes.length > 0" dense>
      <v-col v-for="paciente in pacientes" :key="paciente.idpacientes" cols="12" lg="3" md="4" sm="6" xs="12">
        <v-card outlined >
          <v-list-item three-line>
            <v-list-item-content>
              <v-list-item-title><v-icon left small>mdi-account</v-icon>
                <router-link :to="{ name: 'perfil_paciente', params: { id_paciente: paciente.idpacientes }}">{{ getNomPaciente(paciente) }}</router-link>
              </v-list-item-title>
              <v-list-item-subtitle><v-icon left small>mdi-card-account-details</v-icon>{{ getDocumentoPaciente(paciente) }}</v-list-item-subtitle>
              <v-list-item-subtitle><v-icon left small>mdi-domain</v-icon>{{ getEmpresa(paciente) }}</v-list-item-subtitle>
            </v-list-item-content>
          </v-list-item>
          <v-img max-height="200px" :src="getFotoPaciente(paciente.foto)" contain></v-img>
          <v-divider class="my-1"></v-divider>
          <template v-if="paciente.ficha_paciente.length> 0">
            <v-expansion-panels flat accordion>
              <v-expansion-panel>
                <v-expansion-panel-header>ULTIMA ATENCION: {{ getFechaUltimaAtencion(paciente) }}</v-expansion-panel-header>
                <v-expansion-panel-content>
                  <v-list>
                    <v-list-item v-if="paciente.ficha_paciente[0].pcr_prueba_molecular">
                      <v-list-item-subtitle>PCR</v-list-item-subtitle>
                      <v-list-item-subtitle v-if="paciente.ficha_paciente[0].pcr_prueba_molecular.resultado !== null" class="text-right">
                        <v-chip outlined small dark :color="getColorPcr(paciente.ficha_paciente[0].pcr_prueba_molecular.resultado)">
                          {{ getResultPcr(paciente.ficha_paciente[0].pcr_prueba_molecular.resultado) }}
                        </v-chip>
                      </v-list-item-subtitle>
                    </v-list-item>
                  </v-list>
                  <v-list>
                    <v-list-item v-if="paciente.ficha_paciente[0].prueba_serologica.length > 0">
                      <v-list-item-subtitle>PRS</v-list-item-subtitle>
                      <v-list-item-subtitle class="text-right">
                        <v-chip v-if="paciente.ficha_paciente[0].prueba_serologica[0].resultado"
                                outlined small dark :color="getColorPrs(paciente.ficha_paciente[0].prueba_serologica[0].resultado)"
                        >
                          {{ getResultPrs(paciente.ficha_paciente[0].prueba_serologica[0].resultado) }}
                        </v-chip>
                      </v-list-item-subtitle>
                    </v-list-item>
                  </v-list>
                  <v-list>
                    <v-list-item>
                      <v-list-item-subtitle>Datos Clínicos</v-list-item-subtitle>
                      <v-list-item-subtitle class="text-right">
                        <v-chip dark small outlined color="red darken-2" v-if="paciente.ficha_paciente[0].datos_clinicos.length > 0">SI</v-chip>
                        <v-chip v-else dark small outlined color="green darken-2">NO</v-chip>
                      </v-list-item-subtitle>
                    </v-list-item>
                  </v-list>
                  <v-list>
                    <v-list-item>
                      <v-list-item-subtitle>Antedentes Ep.</v-list-item-subtitle>
                      <v-list-item-subtitle class="text-right">
                        <v-chip dark small outlined color="red darken-2" v-if="paciente.ficha_paciente[0].antecedentes_ep.length > 0">SI</v-chip>
                        <v-chip v-else dark small outlined color="green darken-2">NO</v-chip>
                      </v-list-item-subtitle>
                    </v-list-item>
                  </v-list>
                </v-expansion-panel-content>
              </v-expansion-panel>
            </v-expansion-panels>
          </template>
        </v-card>
      </v-col>
    </v-row>
    <template v-else-if="pacientes && pacientes.length === 0">
      <v-alert
          border="left"
          colored-border
          type="info"
          elevation="2"
          class="mx-auto"
          width="500px"
      >
        SIN COINCIDENCIAS
      </v-alert>
    </template>
    <template v-else>
      <v-sheet :color="`grey ${theme.isDark ? 'darken-2' : 'lighten-4'}`" class="pa-3">
        <v-row>
          <v-col v-for="n in 20" :key="n" cols="12" lg="3" md="4" sm="6" xs="12">
            <v-skeleton-loader type="card"></v-skeleton-loader>
          </v-col>
        </v-row>
      </v-sheet>
    </template>
    <paginate :store="modulo" />
    <DialogReporte :store="modulo" />
  </div>
</template>

<script>
import { extend, ValidationObserver, ValidationProvider, setInteractionMode } from 'vee-validate'
import {required} from "vee-validate/dist/rules";
import buscadorPcrSalud from "../buscadorPcrSalud";
import paginate from "../paginate";
import DialogReporte from "../DialogReporte";
setInteractionMode('eager')
extend('required', {
  ...required,
  message: '{_field_} can not be empty',
})

export default {
  components: {
    DialogReporte,
    ValidationObserver,
    ValidationProvider,
    buscadorPcrSalud,
    paginate,
  },
  inject: {
    theme: {
      default: { isDark: false },
    },
  },
  data() {
    return {
      modulo: "salud",
    }
  },
  methods: {
    ingresarPerfil(id_paciente) {
      this.$store.commit('salud/SET_ID_PACIENTE', id_paciente);
      this.$router.push({ name: 'perfil_paciente', params: { id_paciente: id_paciente } });
    },
    getEmpresa(paciente) {
      let empresa = "";
      if(paciente.empresa) {
        empresa = paciente.empresa.descripcion;
      }
      return empresa;
    },
    getNomPaciente(paciente) {
      let nombres = paciente.nombres;
      let apellido_paterno = paciente.apellido_paterno;
      let apellido_materno = "";
      let nombre_completo = "";

      if(paciente.apellido_materno) {
        apellido_materno = paciente.apellido_materno;
      }
      nombre_completo = nombres + " " + apellido_paterno + " " + apellido_materno;

      return nombre_completo;
    },
    getDocumentoPaciente(paciente) {
      let tipo_documento = "";
      let numero_documento = paciente.numero_documento;
      let doc ="";

      if (paciente.tipo_documento) {
        tipo_documento = Number(paciente.tipo_documento);
        switch (tipo_documento) {
          case 1: tipo_documento = "DNI"; break;
          case 2: tipo_documento = "CE"; break;
          case 3: tipo_documento = "PASAPORTE"; break;
          case 7: tipo_documento = "RUT"; break;
          default: tipo_documento = ""; break;
        }
      }

      doc = tipo_documento + " " + numero_documento;
      return doc;
    },
    getFotoPaciente(path) {
      let src = 'https://www.labicok.com/wp-content/uploads/2020/09/default-user-image.png'
      if(path) {
        src = "/api/v1/fp/" + path
      }
      return src;
    },
    getFechaUltimaAtencion(paciente) {
      let fecha = "";
      if (paciente.ficha_paciente && paciente.ficha_paciente.length > 0) {
        fecha = paciente.ficha_paciente[0].fecha;
      }
      return fecha;
    },
    getColorPcr(resultado) {
      let color = "";
      switch (resultado) {
        case 0: color = "green darken-2"; break;
        case 1: color = "red darken-2"; break;
        case 2: color = "orange darken-2"; break;
      }
      return color;
    },
    getColorPrs(resultado) {
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
    getResultPcr(resultado) {
      let result = "";
      switch (resultado) {
        case 0: result = "NEGATIVO"; break;
        case 1: result = "POSITIVO"; break;
        case 2: result = "ANULADO"; break;
      }
      return result;
    },
    getResultPrs(resultado) {
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
  },
  computed: {
    pacientes: {
      get() {
        return this.$store.state.salud.data.data;
      }
    },
  },
  created() {
    this.$store.dispatch('salud/getFichas', this.$store.state.salud.data.current_page);
  }
}
</script>


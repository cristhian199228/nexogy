<template>
  <div>
    <v-dialog v-model="dialog.paciente" persistent scrollable width="600px">
      <v-card v-if="ficha && ficha.paciente_isos ">
        <v-card-title>{{ficha.paciente_isos.full_name}}</v-card-title>
        <v-card-subtitle>DNI: {{ ficha.paciente_isos.numero_documento }}</v-card-subtitle>
        <v-divider class="mt-0 mb-5"></v-divider>
        <v-card-text style="height: 600px">
          <h6>Resultado PRS</h6>
          <template v-if="ficha.prueba_serologica.length > 0 && ficha.prueba_serologica[0].resultado">
            <v-chip dark :color="getColor(ficha.prueba_serologica[0].resultado)">
              {{ getResultado(ficha.prueba_serologica[0].resultado) }}
            </v-chip>
          </template>
          <br>
          <br>
          <h6>Datos Clinicos</h6>
          <template v-if="datosClinicos.length > 0">
            <v-chip class="mb-1 mr-1" dark color="red darken-2" v-for="(dc,i) in datosClinicos" :key="i">{{ dc }}</v-chip>
          </template>
          <template v-else>
            NO PRESENTA
          </template>
          <br>
          <br>
          <h6>Antecedentes Epidemiológicos</h6>
          <template v-if="antecedentesEp.length > 0">
            <v-chip class="mb-1 mr-1" dark color="red darken-2" v-for="ae in antecedentesEp" :key="ae">{{ ae }}</v-chip>
          </template>
          <template v-else>
            NO PRESENTA
          </template>
          <br>
          <br>
          <h6>Historial de PRS</h6>
          <template v-if="pruebas_rapidas.length > 0">
            <v-timeline v-for="prs in pruebas_rapidas" :key="prs.idpruebaserologicas">
              <v-timeline-item :color="getColor(prs.resultado)" small>
                <template v-slot:opposite>
                  <span>{{ getResult(prs.resultado) }}</span>
                </template>
                {{ prs.fecha }}
              </v-timeline-item>
            </v-timeline>
          </template>
        </v-card-text>
        <v-divider class="my-0"></v-divider>
        <v-card-actions>
          <v-btn text color="normal" @click="close">CERRAR</v-btn>
          <v-spacer></v-spacer>
          <v-btn v-if="!ficha.citas_mw"  color="success" @click="openDialogMw">ENVIAR MW</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import { mapState } from 'vuex'
import { getStrAndColorPrs } from '../modules'

export default {
  props: ['ficha'],
  methods: {
    close() {
      this.$store.commit('super_prs/SHOW_DIALOG_PACIENTE', false);
    },
    openDialogMw() {
      this.$store.commit('super_prs/SHOW_DIALOG_MEDIWEB', true)
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
    getResultado(resultado) {
      let res = "";
      switch (resultado) {
        case 1: res = "NEGATIVO"; break;
        case 2: res = "REACTIVO IGG RECUPERADO"; break;
        case 3: res = "REACTIVO IGG VACUNADO"; break;
        case 4: res = "REACTIVO IGG"; break;
        case 5: res = "REACTIVO IGM"; break;
        case 6: res = "REACTIVO IGM/IGG"; break;
        case 7: res = "REACTIVO IGM PERSISTENTE"; break;
        case 8: res = "REACTIVO IGM/IGG PERSISTENTE"; break;
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
  },
  computed: {
    ...mapState('super_prs',['dialog','pruebas_rapidas']),
    datosClinicos() {
      const dc = [];
      if(this.ficha.datos_clinicos.length > 0) {
        const arrDc = this.ficha.datos_clinicos[0];
        for( const property in arrDc){
          if (arrDc[property] === 1) {
            if (property === "dolor_garganta") {
              dc.push("Dolor de garganta");
            } else if (property === "congestion_nasal") {
              dc.push("Congestión nasal");
            } else if (property === "anosmia_ausegia") {
              dc.push("Anosmia/Ausegia");
            } else if (property === "cefalea") {
              dc.push("Cefálea");
            } else if (property === "diarrea") {
              dc.push("Diarrea");
            } else if (property === "dificultad_respiratoria") {
              dc.push("Dificultad respiratoria");
            } else if (property === "falta_aliento") {
              dc.push("Falta de aliento");
            } else if (property === "fiebre") {
              dc.push("Fiebre");
            } else if (property === "irritabilidad_confusion") {
              dc.push("Irritabilidad/Confusión");
            } else if (property === "malestar_general") {
              dc.push("Malestar General");
            } else if (property === "nauseas_vomitos") {
              dc.push("Náuseas/Vómitos");
            } else if (property === "tos") {
              dc.push("Tos");
            } else if (property === "post_vacunado") {
              dc.push("Sintomático post vacunación");
            }
          } else if (arrDc[property]) {
            if (property === "otros") {
              dc.push(`Otros: ${arrDc[property]}`);
            } else if (property === "toma_medicamento") {
              dc.push(`medicamento: ${arrDc[property]}`);
            } else if (property === "fecha_inicio_sintomas") {
              dc.push(`Fecha de inicio de síntomas: ${arrDc[property]}`);
            }
          }
        }
      }
      return dc;
    },
    antecedentesEp(){
      const ae = [];
      if(this.ficha.antecedentes_ep.length > 0) {
        const arrAe = this.ficha.antecedentes_ep[0];
        for( const property in arrAe){
          if (arrAe[property] === 1) {
            if (property === "contacto_cercano") {
              ae.push("Contacto cercano con investigado covid");
            } else if (property === "conv_covid") {
              ae.push("Conversación con investigado covid");
            } else if (property === "dias_viaje") {
              ae.push("Viajó en los últimos 14 dias");
            }
          } else if (arrAe[property] !== null) {
            if (property === "fecha_llegada") {
              ae.push(`Fecha de llegada viaje: ${arrAe[property]}`);
            } else if (property === "paises_visitados") {
              ae.push(`Lugares visitados: ${arrAe[property]}`);
            } else if (property === "medio_transporte") {
              ae.push(`Medio de transporte: ${arrAe[property]}`);
            } else if(property === "debilite_sistema" ) {
              ae.push(`Condición que debilite sistema: ${arrAe[property]}`);
            } else if(property === "fecha_ultimo_contacto" ) {
              ae.push(`Fecha de ultimo contacto con investigado covid: ${arrAe[property]}`);
            }
          }
        }
      }
      return ae;
    },
  }
}
</script>

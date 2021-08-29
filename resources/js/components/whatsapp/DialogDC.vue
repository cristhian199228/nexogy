<template>
  <div>
    <v-dialog v-model="showDialog" width="500px" scrollable>
      <v-card>
        <v-card-title>DATOS CLÍNICOS</v-card-title>
        <v-divider></v-divider>
        <v-card-text>
          <v-chip class="mb-1 mr-1" dark color="red darken-2" v-for="(dc,i) in datosClinicos" :key="i">{{ dc }}</v-chip>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text color="normal" @click="close">CERRAR</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
export default {
  props: {
    arrDc: Object,
    showDialog: {
      type: Boolean,
      default: false,
    }
  },
  methods: {
    close() {
      this.$emit('close-dialog')
    }
  },
  computed: {
    datosClinicos() {
      const dc = [];
      for( const property in this.arrDc){
        if (this.arrDc[property] === 1) {
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
        } else if (this.arrDc[property] !== null) {
          if (property === "otros") {
            dc.push(`Otros: ${this.arrDc[property]}`);
          } else if (property === "toma_medicamento") {
            dc.push(`medicamento: ${this.arrDc[property]}`);
          } else if (property === "fecha_inicio_sintomas") {
            dc.push(`Fecha de inicio de síntomas: ${this.arrDc[property]}`);
          }
        }
      }
      return dc;
    },
  }
}
</script>

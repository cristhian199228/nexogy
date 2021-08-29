<template>
  <div>
    <v-dialog v-model="showDialog" width="500px" scrollable>
      <v-card>
        <v-card-title>ANTECEDENTES EPIDEMIOLÓGICOS</v-card-title>
        <v-divider></v-divider>
        <v-card-text>
          <v-chip class="mb-1 mr-1" dark color="red darken-2" v-for="ae in antecedentesEp" :key="ae">{{ ae }}</v-chip>
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
    arrAe: Object,
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
    antecedentesEp(){
      const ae = [];
      for( const property in this.arrAe){
        if (this.arrAe[property] === 1) {
          if (property === "contacto_cercano") {
            ae.push("Contacto cercano con investigado covid");
          } else if (property === "conv_covid") {
            ae.push("Conversación con investigado covid");
          } else if (property === "dias_viaje") {
            ae.push("Viajó en los últimos 14 dias");
          }
        } else if (this.arrAe[property] !== null) {
          if (property === "fecha_llegada") {
            ae.push(`Fecha de llegada viaje: ${this.arrAe[property]}`);
          } else if (property === "paises_visitados") {
            ae.push(`Lugares visitados: ${this.arrAe[property]}`);
          } else if (property === "medio_transporte") {
            ae.push(`Medio de transporte: ${this.arrAe[property]}`);
          } else if(property === "debilite_sistema" ) {
            ae.push(`Condición que debilite sistema: ${this.arrAe[property]}`);
          } else if(property === "fecha_ultimo_contacto" ) {
            ae.push(`Fecha de ultimo contacto con investigado covid: ${this.arrAe[property]}`);
          }
        }
      }
      return ae;
    },
  }
}
</script>

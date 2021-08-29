<template>
  <div>
    <v-dialog v-model="dialog.reporte" max-width="700px" persistent>
      <v-card>
        <validation-observer ref="observer" v-slot="{invalid}">
          <v-card-title>GENERAR REPORTE</v-card-title>
          <v-divider class="my-0"></v-divider>
          <v-card-text>
            <v-row>
              <v-col cols="12" sm="6">
                <v-date-picker v-model="dates" range></v-date-picker>
              </v-col>
              <v-col cols="12" sm="6">
                <validation-provider v-slot="{errors}" name="Date range" rules="required|length:2">
                  <v-text-field
                    v-model="dateRangeText"
                    label="Rango de Fechas"
                    prepend-icon="mdi-calendar"
                    readonly
                    :error-messages="errors"
                  ></v-text-field>
                </validation-provider>
              </v-col>
            </v-row>
          </v-card-text>
          <v-divider class="my-0"></v-divider>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn text color="normal" @click="close">CANCELAR</v-btn>
            <v-btn :disabled="invalid || invalidDate" color="primary" @click="generarReporte()">
              <v-icon left>mdi-microsoft-excel</v-icon>GENERAR</v-btn>
          </v-card-actions>
        </validation-observer>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import { mapState } from 'vuex'

export default {
  props: ['store'],
  data() {
    return {
      dates: [],
    }
  },
  computed: {
    ...mapState(['dialog']),
    dateRangeText () {
      return this.dates;
    },
    invalidDate () {
      return this.dates.length > 1 && this.dates[0] > this.dates[1];
    }
  },
  methods: {
    close() {
      this.$refs.observer.reset();
      this.dates = [];
      this.$store.commit('SHOW_DIALOG_REPORTE', false);
    },
    generarReporte() {
      this.$refs.observer.validate().then(valid => {
        if(valid) {
          window.open( '/api/v1/'+ this.store +'/' + this.dates[0]+ "/" + this.dates[1] , "_blank");
        }
      });
    },
  }
}
</script>

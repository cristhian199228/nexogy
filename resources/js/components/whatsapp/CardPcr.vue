<template>
  <div>
    <v-card outlined>
      <v-card-title>Historial PCR</v-card-title>
      <v-divider class="my-1"></v-divider>
      <v-card-text v-scroll.self="onScroll" style="max-height: 600px;" class="overflow-y-auto">
        <template v-if="arrPcr.length > 0">
          <v-timeline v-for="pcr in arrPcr" :key="pcr.idpcr_pruebas_moleculares">
            <v-timeline-item :color="getColorPcr(pcr.resultado)" small>
              <template v-slot:opposite>
                <span><small>{{getResultPcr(pcr.resultado)}}</small></span>
              </template>
              <small>{{ pcr.fecha2 }}</small>
            </v-timeline-item>
          </v-timeline>
        </template>
        <template v-else>
          <div class="text-center">
            NO SE REGISTRAN PCR
          </div>
        </template>
      </v-card-text>
    </v-card>
  </div>
</template>

<script>
export default {
  data() {
    return {
      scrollInvoked: 0,
    }
  },
  props: {
    arrPcr: Array,
  },
  methods: {
    onScroll () {
      this.scrollInvoked++
    },
    getResultPcr(resultado) {
      let res = "";
      switch (resultado) {
        case 0: res = "NEG"; break;
        case 1: res = "POS"; break;
        case 2: res = "ANULADO"; break;
      }
      return res;
    },
    getColorPcr(resultado) {
      let res = "";
      switch (resultado) {
        case 0: res = "green darken-2"; break;
        case 1: res = "red darken-2"; break;
        case 2: res = "orange darken-2"; break;
      }
      return res;
    },
  }
}
</script>

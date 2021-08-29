<template>
  <div>
    <v-card outlined>
      <v-card-title>HISTORIAL DE PRUEBAS</v-card-title>
      <v-divider class="my-1"></v-divider>
      <v-card-text v-scroll.self="onScroll" style="max-height: 675px;" class="overflow-y-auto">
        <template v-if="arrPrs.length > 0">
          <v-timeline v-for="prueba in arrPrs" :key="prueba.id">
            <v-timeline-item :color="color(prueba)" small>
              <template v-slot:opposite>
                <span><small><span class="font-weight-bold">{{ prueba.tipo }}</span> - {{ resultado(prueba) }}</small></span>
              </template>
              <small>{{ moment(prueba.fecha).format('DD/MM/YYYY') }}</small>
            </v-timeline-item>
          </v-timeline>
        </template>
        <template v-else>
          <div class="text-center">
            NO SE REGISTRAN PRUEBAS
          </div>
        </template>
      </v-card-text>
    </v-card>
  </div>
</template>

<script>
import moment from "moment";
import { mapGetters } from 'vuex'
export default {
  props: {
    arrPrs: Array
  },
  data() {
    return {
      scrollInvoked: 0,
      moment,
    }
  },
  computed: {
    ...mapGetters([
      'getPcrStrResult',
      'getPrsStrResult',
      'getPrsColorResult',
      'getPcrColorResult',
    ]),
    ...mapGetters('pa',[
      'resultadoPa',
      'colorPa'
    ])
  },
  methods: {
    onScroll () {
      this.scrollInvoked++
    },
    color(prueba) {
      if (prueba.tipo === 'PRS') return this.getPrsColorResult(prueba.resultado)
      if (prueba.tipo === 'PCR') return this.getPcrColorResult(prueba.resultado)
      return this.colorPa(prueba.resultado)
    },
    resultado(prueba) {
      if (prueba.tipo === 'PRS') return this.getPrsStrResult(prueba.resultado)
      if (prueba.tipo === 'PCR') return this.getPcrStrResult(prueba.resultado)
      return this.resultadoPa(prueba.resultado)
    }
  },
}
</script>

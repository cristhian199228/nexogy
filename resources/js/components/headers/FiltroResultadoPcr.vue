<template>
  <div>
    <v-select v-model="resultado" :items="resultados" label="PCR" dense hide-details clearable
              item-value="value" item-text="text" @click:clear="clear">
    </v-select>
  </div>
</template>

<script>
export default {
  props: ['module'],
  data() {
    return {
      resultados: [
        { value: 0, text: "NEGATIVO" },
        { value: 1, text: "POSITIVO" },
        { value: 2, text: "ANULADO" },
        { value: 3, text: "SIN RESULTADO" },
      ],
    }
  },
  methods: {
    getFichas() {
      this.$store.dispatch(`${this.module}/getFichas`, 1)
    },
    clear() {
      this.$store.commit(`${this.module}/SET_FILTRO_RESULTADO`, null)
      this.getFichas()
    }
  },
  computed: {
    resultado: {
      get() {
        return this.$store.state[this.module].filtros.resultado
      },
      set(val) {
        this.$store.commit(`${this.module}/SET_FILTRO_RESULTADO`, val)
        this.getFichas()
      }
    },
  }
}
</script>
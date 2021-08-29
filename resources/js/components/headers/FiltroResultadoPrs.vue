<template>
  <div>
    <v-select v-model="resultado" :items="resultados" label="PRS" dense hide-details clearable
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
        {value: 1, text: 'NEG'},
        {value: 2, text: 'IGG R'},
        {value: 3, text: 'IGG V'},
        {value: 4, text: 'IGG'},
        {value: 5, text: 'IGM'},
        {value: 6, text: 'IGM/IGG'},
        {value: 7, text: 'IGM P'},
        {value: 8, text: 'IGM/IGG P'},
      ],
    }
  },
  methods: {
    getFichas() {
      this.$store.dispatch(`${this.module}/getFichas`, 1)
    },
    clear() {
      this.$store.commit(`${this.module}/SET_FILTRO_RESULTADO_PRS`, null)
      this.getFichas()
    }
  },
  computed: {
    resultado: {
      get() {
        return this.$store.state[this.module].filtros.prs
      },
      set(val) {
        this.$store.commit(`${this.module}/SET_FILTRO_RESULTADO_PRS`, val)
        this.getFichas()
      }
    },
  }
}
</script>
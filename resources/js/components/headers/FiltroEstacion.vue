<template>
  <div>
    <v-select class="font-weight-medium" hide-details v-model="estacion" label="Estacion" dense :items="estaciones"
              item-value="idestaciones" item-text="nom_estacion" clearable></v-select>
  </div>
</template>

<script>
export default {
  props: ['module'],
  methods: {
    getFichas() {
      this.$store.dispatch(`${this.module}/getFichas`, 1)
    },
    clear() {
      this.$store.commit(`${this.module}/SET_FILTRO_ESTACION`, null)
      this.getFichas()
    }
  },
  computed: {
    estacion: {
      get() {
        return this.$store.state[this.module].filtros.estacion
      },
      set(val) {
        this.$store.commit(`${this.module}/SET_FILTRO_ESTACION`, val)
        this.getFichas()
      }
    },
    estaciones() {
      return this.$store.state.estaciones.filter(estacion => estacion.idsede > 3 || estacion.idestaciones === 11)
    }
  }
}
</script>
<template>
  <div>
    <v-text-field dense hide-details v-model="buscar" label="De" append-icon="mdi-magnify" @click:append="getFichas"
                  @keyup.enter="getFichas" clearable @click:clear="clear" single-line></v-text-field>
  </div>
</template>

<script>
export default {
  props: ['module'],
  computed: {
    buscar: {
      get() {
        return this.$store.state[this.module].filtros.buscar
      },
      set(val) {
        this.$store.commit(`${this.module}/SET_CRITERIO_BUSQUEDA`, val)
      }
    },
  },
  methods: {
    getFichas() {
      this.$store.dispatch(`${this.module}/getFichas`, 1)
    },
    clear() {
      this.$store.commit(`${this.module}/SET_CRITERIO_BUSQUEDA`, null)
      this.getFichas()
    }
  }
}
</script>
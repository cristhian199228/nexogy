<template>
  <div>
    <v-text-field dense hide-details v-model="empresa" label="Empresa" append-icon="mdi-magnify" @click:append="getFichas"
                  @keyup.enter="getFichas" clearable @click:clear="clear" single-line></v-text-field>
  </div>
</template>

<script>
export default {
  props: ['module'],
  computed: {
    empresa: {
      get() {
        return this.$store.state[this.module].filtros.empresa
      },
      set(val) {
        this.$store.commit(`${this.module}/SET_FILTRO_EMPRESA`, val)
      }
    },
  },
  methods: {
    getFichas() {
      this.$store.dispatch(`${this.module}/getFichas`, 1)
    },
    clear() {
      this.$store.commit(`${this.module}/SET_FILTRO_EMPRESA`, null)
      this.getFichas()
    }
  }
}
</script>
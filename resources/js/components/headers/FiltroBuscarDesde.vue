<template>
  <div>
    <v-text-field dense hide-details v-model="desde" label="De" append-icon="mdi-magnify" @click:append="getFichas"
                  @keyup.enter="getFichas" clearable @click:clear="clear" ></v-text-field>
  </div>
</template>

<script>
export default {
  props: ['module'],
  computed: {
    desde: {
      get() {
        return this.$store.state[this.module].filtros.desde
      },
      set(val) {
        this.$store.commit(`${this.module}/SET_FILTRO_DESDE`, val)
      }
    },
  },
  methods: {
    getFichas() {
      this.$store.dispatch(`${this.module}/getFichas`, 1)
    },
    clear() {
      this.$store.commit(`${this.module}/SET_FILTRO_DESDE`, null)
      this.getFichas()
    }
  }
}
</script>
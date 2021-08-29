<template>
  <div>
    <v-select v-model="sede" :items="sedes" label="Sede" dense hide-details clearable
              item-value="idsedes" item-text="descripcion" @click:clear="clear">
    </v-select>
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
      this.$store.commit(`${this.module}/SET_FILTRO_SEDE`, null)
      this.getFichas()
    }
  },
  computed: {
    sedes() {
      return this.$store.state.sedes
    },
    sede: {
      get() {
        return this.$store.state[this.module].filtros.sede
      },
      set(val) {
        this.$store.commit(`${this.module}/SET_FILTRO_SEDE`, val)
        this.getFichas()
      }
    },
  }
}
</script>
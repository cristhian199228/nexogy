<template>
  <div>
    <v-text-field dense hide-details v-model="para" label="Para" append-icon="mdi-magnify" @click:append="getFichas"
                  @keyup.enter="getFichas" clearable @click:clear="clear" ></v-text-field>
  </div>
</template>

<script>
export default {
  props: ['module'],
  computed: {
    para: {
      get() {
        return this.$store.state[this.module].filtros.para
      },
      set(val) {
        this.$store.commit(`${this.module}/SET_FILTRO_PARA`, val)
      }
    },
  },
  methods: {
    getFichas() {
      this.$store.dispatch(`${this.module}/getFichas`, 1)
    },
    clear() {
      this.$store.commit(`${this.module}/SET_FILTRO_PARA`, null)
      this.getFichas()
    }
  }
}
</script>
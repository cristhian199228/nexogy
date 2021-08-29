<template>
  <div>
    <v-select v-model="tipo" :items="tipos" label="Tipo" dense hide-details clearable
              item-value="idtipo_prueba_molecular" item-text="descripcion" @click:clear="clear">
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
      this.$store.commit(`${this.module}/SET_FILTRO_TIPO`, null)
      this.getFichas()
    }
  },
  computed: {
    tipo: {
      get() {
        return this.$store.state[this.module].filtros.tipo
      },
      set(val) {
        this.$store.commit(`${this.module}/SET_FILTRO_TIPO`, val)
        this.getFichas()
      }
    },
    tipos() {
      return this.$store.state.admin_pcr.tipos
    }
  }
}
</script>
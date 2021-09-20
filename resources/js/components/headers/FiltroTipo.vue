<template>
  <div>
    <v-select
      v-model="direccion"
      :items="items"
      label="Dirección"
      dense
      hide-details
      clearable
      item-value="idtipo"
      item-text="descripcion"
      @click:clear="clear"
    >
    </v-select>
  </div>
</template>

<script>
export default {
  data: () => ({
    items: [
      { idtipo: "Incoming", descripcion: "ENTRANTE" },
      { idtipo: "Outgoing", descripcion: "SALIENTE" },
    ],
  }),

  props: ["module"],
   methods: {
    getFichas() {
      this.$store.dispatch(`${this.module}/getFichas`, 1)
    },
    clear() {
      this.$store.commit(`${this.module}/SET_FILTRO_DIRECCION`, null)
      this.getFichas()
    }
  },
  computed: {
    direccion: {
     get() {
        return this.$store.state[this.module].filtros.direccion;
      },
      set(val) {
        this.$store.commit(`${this.module}/SET_FILTRO_DIRECCION`, val);
        this.getFichas();
      },
    },
  },
};
</script>
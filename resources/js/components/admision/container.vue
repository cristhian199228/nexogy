<template>
  <div>
    <v-card outlined>
      <router-view></router-view>
    </v-card>
    <SelectEstacion :modulo="modulo" />
  </div>
</template>

<script>
import { mapState } from 'vuex';
import SelectEstacion from "../SelectEstacion";
import buscador from "../buscador";

export default {
  components: {
    SelectEstacion,
    buscador
  },
  data() {
    return {
      modulo: "admision",
    }
  },
  computed: {
    ...mapState(['id_estacion']),
    ...mapState('currentUser',['user']),
  },
  created() {
    if (!this.id_estacion) this.$store.commit('SHOW_DIALOG_ESTACION', true);
  }
}
</script>

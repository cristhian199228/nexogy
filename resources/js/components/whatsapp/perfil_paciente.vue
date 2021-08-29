<template>
  <div>
    <template v-if="paciente">
      <v-row dense>
        <v-col cols="12" lg="6" md="6" sm="6" xs="12">
          <CardPaciente :patient-info="paciente" />
        </v-col>
        <v-col cols="12" lg="6" md="6" sm="6" xs="12">
          <CardPrs :arr-prs="paciente.pruebas" />
        </v-col>
<!--        <v-col cols="12" lg="4" md="4" sm="12" xs="12">
          <CardPcr :arr-pcr="paciente.pruebas_moleculares" />
        </v-col>-->
      </v-row>
      <v-row dense>
        <v-col cols="12">
          <TablaAtenciones :arr-fichas="paciente.ficha_paciente" />
        </v-col>
      </v-row>
    </template>
    <template v-else>
      <LoaderPerfil />
    </template>
  </div>
</template>

<script>
import CardPcr from "./CardPcr";
import CardPrs from "./CardPrs";
import CardPaciente from "./CardPaciente";
import TablaAtenciones from "./TablaAtenciones";
import LoaderPerfil from "./LoaderPerfil";
import { mapState } from 'vuex';

export default {
  components: {
    CardPcr,
    CardPrs,
    CardPaciente,
    TablaAtenciones,
    LoaderPerfil
  },
  computed: {
    ...mapState('salud',['paciente']),
  },
  created() {
    this.$store.dispatch('salud/getPatientInfo', this.$route.params.id_paciente);
  }
}
</script>

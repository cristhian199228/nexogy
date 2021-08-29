<template>
  <div>
    <buscador-pcr-salud :store="modulo" title="PACIENTES" />
    <v-data-table :headers="headers" :items="getPacientes" :loading="loading_table" :items-per-page="15" hide-default-footer :page.sync="getCurrentPage">
      <template v-slot:item.contador="{item}">
        <small>{{ item.contador }}</small>
      </template>
      <template v-slot:item.nom_completo="{item}">
        <small>{{ item.nom_completo }}</small>
      </template>
      <template v-slot:item.tipo_documento="{item}">
        <small>{{ getStrTipoDocumento(Number(item.tipo_documento))  }}</small>
      </template>
      <template v-slot:item.dni="{item}">
        <small>{{ item.numero_documento }}</small>
      </template>
      <template v-slot:item.emp="{item}">
        <small>{{ item.empresa ? item.empresa.descripcion : '' }}</small>
      </template>
      <template v-slot:item.reg="{item}">
        <small>{{ item.nro_registro }}</small>
      </template>
      <template v-slot:item.actions="{item}">
        <v-btn small icon :to="{ name: 'EditarPaciente', params: { id_pac: item.idpacientes }}"><v-icon small>mdi-pencil</v-icon></v-btn>
        <v-btn small icon @click="eliminar(item)"><v-icon small>mdi-delete</v-icon></v-btn>
      </template>
    </v-data-table>
    <paginate :store="modulo" />
  </div>
</template>

<script>
import {mapState, mapGetters, mapActions } from 'vuex'
import buscadorPcrSalud from "../../buscadorPcrSalud";
import paginate from "../../paginate";

export default {
  components: {
    buscadorPcrSalud,
    paginate
  },
  data() {
    return {
      headers: [
        { text: 'N°', align: 'start', value: 'contador', sortable: false  },
        { text: 'Nombres', value: 'nom_completo', sortable: false },
        { text: 'Tipo doc.', value: 'tipo_documento', sortable: false },
        { text: 'DNI', value: 'dni', sortable: false },
        { text: 'Empresa', value: 'emp', sortable: false },
        { text: 'Registro', value: 'reg', sortable: false },
        { text: 'Acciones', value: 'actions', sortable: false }
      ],
      modulo: 'pacientes',
    }
  },
  computed: {
    ...mapGetters('pacientes',['getCurrentPage','getPacientes']),
    ...mapGetters(['getStrTipoDocumento']),
    ...mapState('pacientes',['loading_table']),
  },
  methods: {
    ...mapActions('pacientes',['getFichas','deletePaciente']),
    eliminar(paciente){
      const r = confirm("Esta seguro de eliminar al paciente?")
      if (r) this.deletePaciente(paciente);
    }
  },
  created() {
    this.getFichas(this.getCurrentPage)
  }
}
</script>
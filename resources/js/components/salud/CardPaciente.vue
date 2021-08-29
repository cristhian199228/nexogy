<template>
  <div>
    <v-card outlined>
      <v-card-title>{{ getNomPaciente(patientInfo) }}</v-card-title>
      <v-card-subtitle>{{ getDocumentoPaciente(patientInfo)}} &nbsp;/&nbsp; EDAD: {{ patientInfo.edad }}</v-card-subtitle>
      <v-divider class="my-1"></v-divider>
      <v-img :src="getFotoPaciente(patientInfo.foto)" max-height="300px" contain></v-img>
      <v-card-text>
        <v-list>
          <v-list-item v-if="patientInfo.empresa">
            <v-list-item-icon>
              <v-icon color="indigo">mdi-domain</v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title>{{ patientInfo.empresa.descripcion }}</v-list-item-title>
              <v-list-item-subtitle>RUC: {{ patientInfo.empresa.ruc }}</v-list-item-subtitle>
            </v-list-item-content>
          </v-list-item>
          <v-list-item v-if="patientInfo.puesto">
            <v-list-item-icon>
              <v-icon color="indigo">mdi-account-hard-hat</v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title>{{ patientInfo.puesto }}</v-list-item-title>
              <v-list-item-subtitle>Puesto</v-list-item-subtitle>
            </v-list-item-content>
          </v-list-item>
          <v-list-item v-if="patientInfo.celular">
            <v-list-item-icon>
              <v-icon color="indigo">mdi-phone</v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title>{{ patientInfo.celular }}</v-list-item-title>
              <v-list-item-subtitle>Móvil</v-list-item-subtitle>
            </v-list-item-content>
          </v-list-item>
          <v-list-item v-if="patientInfo.correo">
            <v-list-item-icon>
              <v-icon color="indigo">mdi-email</v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title>{{ patientInfo.correo.toLowerCase() }}</v-list-item-title>
              <v-list-item-subtitle>Correo</v-list-item-subtitle>
            </v-list-item-content>
          </v-list-item>
          <v-list-item v-if="patientInfo.direccion">
            <v-list-item-icon>
              <v-icon color="indigo">mdi-map-marker</v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title>{{ patientInfo.direccion }}</v-list-item-title>
              <v-list-item-subtitle>Dirección</v-list-item-subtitle>
            </v-list-item-content>
          </v-list-item>
        </v-list>
      </v-card-text>
    </v-card>
  </div>
</template>

<script>
export default {
  props: {
    patientInfo: Object,
  },
  methods: {
    getNomPaciente(paciente) {
      let nombres = paciente.nombres;
      let apellido_paterno = paciente.apellido_paterno;
      let apellido_materno = "";
      let nombre_completo = "";

      if(paciente.apellido_materno) {
        apellido_materno = paciente.apellido_materno;
      }
      nombre_completo = nombres + " " + apellido_paterno + " " + apellido_materno;

      return nombre_completo;
    },
    getDocumentoPaciente(paciente) {
      let tipo_documento = "";
      let numero_documento = paciente.numero_documento;
      let doc ="";

      if (paciente.tipo_documento) {
        tipo_documento = Number(paciente.tipo_documento);
        switch (tipo_documento) {
          case 1: tipo_documento = "DNI"; break;
          case 2: tipo_documento = "CE"; break;
          case 3: tipo_documento = "PASAPORTE"; break;
          case 7: tipo_documento = "RUT"; break;
          default: tipo_documento = ""; break;
        }
      }

      doc = tipo_documento + " " + numero_documento;
      return doc;
    },
    getFotoPaciente(path) {
      let src = "https://www.labicok.com/wp-content/uploads/2020/09/default-user-image.png";
      if(path) {
        src = "/api/v1/fp/" + path
      }
      return src;
    },
  }
}
</script>

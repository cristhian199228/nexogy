<template>
  <div>
    <v-list dense v-if="user && user.roles && user.roles.length > 0">
      <template
        v-if="
          user.roles.includes('Operaciones') ||
          user.roles.includes('SupervisorPcrPrs') ||
          user.roles.includes('Admin')
        "
      >
        <v-list-item link to="/admision">
          <v-list-item-action>
            <v-icon>mdi-doctor</v-icon>
          </v-list-item-action>
          <v-list-item-content>
            <v-list-item-title>Admision</v-list-item-title>
          </v-list-item-content>
        </v-list-item>
        <v-list-item link to="/controlador">
          <v-list-item-action>
            <v-icon>mdi-toolbox</v-icon>
          </v-list-item-action>
          <v-list-item-content>
            <v-list-item-title>Controlador PRS</v-list-item-title>
          </v-list-item-content>
        </v-list-item>
        <v-list-item link to="/pruebas_antigenas">
          <v-list-item-action>
            <v-icon>mdi-cassette</v-icon>
          </v-list-item-action>
          <v-list-item-content>
            <v-list-item-title>Controlador AG</v-list-item-title>
          </v-list-item-content>
        </v-list-item>
        <v-list-item link to="/pcr">
          <v-list-item-action>
            <v-icon>mdi-test-tube-empty</v-icon>
          </v-list-item-action>
          <v-list-item-content>
            <v-list-item-title>Controlador PCR</v-list-item-title>
          </v-list-item-content>
        </v-list-item>
        <v-list-item link to="/supervisor">
          <v-list-item-action>
            <v-icon>mdi-account-supervisor-circle</v-icon>
          </v-list-item-action>
          <v-list-item-content>
            <v-list-item-title>Supervisor PRS</v-list-item-title>
          </v-list-item-content>
        </v-list-item>
      </template>
      <v-list-item
        v-if="
          user.roles.includes('SupervisorPcrPrs') ||
          user.roles.includes('SupervisorPcr') ||
          user.roles.includes('AdminPcr') ||
          user.roles.includes('Admin')
        "
        link
        to="/supervisor_pcr"
      >
        <v-list-item-action>
          <v-icon>mdi-test-tube-empty</v-icon>
        </v-list-item-action>
        <v-list-item-content>
          <v-list-item-title>Supervisor PCR</v-list-item-title>
        </v-list-item-content>
      </v-list-item>
      <v-list-item
        v-if="user.roles.includes('AdminPcr') || user.roles.includes('Admin')"
        link
        to="/administrador_pcr"
      >
        <v-list-item-action>
          <v-icon>mdi-test-tube</v-icon>
        </v-list-item-action>
        <v-list-item-content>
          <v-list-item-title>Administrador PCR</v-list-item-title>
        </v-list-item-content>
      </v-list-item>
      <v-list-item v-if="user.roles.includes('Admin')" link to="/whatsapp">
        <v-list-item-action>
          <v-icon>mdi-whatsapp</v-icon>
        </v-list-item-action>
        <v-list-item-content>
          <v-list-item-title>Whatsapp</v-list-item-title>
        </v-list-item-content>
      </v-list-item>
      <v-list-group
        v-if="
          user.roles.includes('SupervisorPcrPrs') ||
          user.roles.includes('Admin')
        "
        :value="false"
      >
        <template v-slot:activator>
          <v-list-item-action>
            <v-icon>mdi-chart-bar-stacked</v-icon>
          </v-list-item-action>
          <v-list-item-content>
            <v-list-item-title>Estadisticas</v-list-item-title>
          </v-list-item-content>
        </template>

        <v-list-group :value="false" no-action sub-group>
          <template v-slot:activator>
            <v-list-item-content>
              <v-list-item-title>Desempeño</v-list-item-title>
            </v-list-item-content>
          </template>

          <v-list-item link to="/estadisticas_rend">
            <v-list-item-title>Por Estacion</v-list-item-title>

            <v-list-item-icon>
              <v-icon>mdi-poll</v-icon>
            </v-list-item-icon>
          </v-list-item>
        </v-list-group>
        <v-list-group :value="false" no-action sub-group>
          <template v-slot:activator>
            <v-list-item-content>
              <v-list-item-title>Tiempos</v-list-item-title>
            </v-list-item-content>
          </template>

          <v-list-item link to="/estadisticas">
            <v-list-item-title>Atenciones</v-list-item-title>

            <v-list-item-icon>
              <v-icon>mdi-account-supervisor</v-icon>
            </v-list-item-icon>
          </v-list-item>
        </v-list-group>
      </v-list-group>

      <v-list-item
        v-if="
          user.roles.includes('ResponceCenter') ||
          user.roles.includes('CerroVerde') ||
          user.roles.includes('SupervisorPcrPrs') ||
          user.roles.includes('Salud') ||
          user.roles.includes('Admin')
        "
        link
        to="/salud"
      >
        <v-list-item-action>
          <v-icon>mdi-hospital-building</v-icon>
        </v-list-item-action>
        <v-list-item-content>
          <v-list-item-title>Salud</v-list-item-title>
        </v-list-item-content>
      </v-list-item>
      <v-list-item
        v-if="
          user.roles.includes('ResponceCenter') || user.roles.includes('Admin')
        "
        link
        to="/rc"
      >
        <v-list-item-action>
          <v-icon>mdi-headphones</v-icon>
        </v-list-item-action>
        <v-list-item-content>
          <v-list-item-title>Evidencias</v-list-item-title>
        </v-list-item-content>
      </v-list-item>
      <v-list-item
        v-if="
          user.roles.includes('ResponceCenter') ||
          user.roles.includes('SupervisorPcrPrs') ||
          user.roles.includes('Admin')
        "
        link
        to="/is"
      >
        <v-list-item-action>
          <v-icon>mdi-xml</v-icon>
        </v-list-item-action>
        <v-list-item-content>
          <v-list-item-title>Inteligencia Sanitaria</v-list-item-title>
        </v-list-item-content>
      </v-list-item>
    </v-list>
    <v-list dense v-else-if="user && !user.roles">
      <v-list-item link to="/admision">
        <v-list-item-action>
          <v-icon>mdi-doctor</v-icon>
        </v-list-item-action>
        <v-list-item-content>
          <v-list-item-title>Admision</v-list-item-title>
        </v-list-item-content>
      </v-list-item>
      <v-list-item link to="/controlador">
        <v-list-item-action>
          <v-icon>mdi-toolbox</v-icon>
        </v-list-item-action>
        <v-list-item-content>
          <v-list-item-title>Controlador PRS</v-list-item-title>
        </v-list-item-content>
      </v-list-item>
      <v-list-item link to="/pcr">
        <v-list-item-action>
          <v-icon>mdi-test-tube-empty</v-icon>
        </v-list-item-action>
        <v-list-item-content>
          <v-list-item-title>Controlador PCR</v-list-item-title>
        </v-list-item-content>
      </v-list-item>
      <v-list-item link to="/supervisor">
        <v-list-item-action>
          <v-icon>mdi-account-supervisor-circle</v-icon>
        </v-list-item-action>
        <v-list-item-content>
          <v-list-item-title>Supervisor PRS</v-list-item-title>
        </v-list-item-content>
      </v-list-item>
    </v-list>
    <v-list v-else> </v-list>
  </div>
</template>

<script>
import { mapState } from "vuex";
export default {
  data() {
    return {
      admins: [
        ["Management", "mdi-account-multiple-outline"],
        ["Settings", "mdi-cog-outline"],
      ],
      cruds: [
        ["Create", "mdi-plus-outline"],
        ["Read", "mdi-file-outline"],
        ["Update", "mdi-update"],
        ["Delete", "mdi-delete"],
      ],
    };
  },
  computed: {
    ...mapState("currentUser", ["user"]),
  },
};
</script>
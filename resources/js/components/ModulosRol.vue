<template>
  <div>
    <v-list dense v-if="user && user.roles && user.roles.length > 0">
      
    
      <v-list-item
        v-if="user.roles.includes('AdminPcr') || user.roles.includes('Admin')"
        link
        to="/administrador_pcr"
      >
        <v-list-item-action>
          <v-icon>mdi-phone</v-icon>
        </v-list-item-action>
        <v-list-item-content>
          <v-list-item-title>Llamadas</v-list-item-title>
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
<template>
  <v-app id="inspire">
    <v-snackbar text transition="scale-transition" top :color="snackbar.color" v-model="snackbar.show" timeout="3500">
      {{ snackbar.message }}
    </v-snackbar>
    <v-main>
      <v-container fluid class="pa-2">
        <template v-if="user">
          <router-view></router-view>
        </template>
      </v-container>
    </v-main>

    <v-navigation-drawer v-model="drawer" app clipped>
      <ModulosRol />
    </v-navigation-drawer>

    <v-app-bar color="#1E286C" dark app clipped-left>
      <v-app-bar-nav-icon @click.stop="drawer = !drawer"></v-app-bar-nav-icon>
      <v-toolbar-title>SISTEMA DE CONTROL COVID 19</v-toolbar-title>
      <v-spacer></v-spacer>
      <v-menu
        v-model="menu"
        :close-on-content-click="false"
        offset-y
        max-width="400px"
      >
        <template v-slot:activator="{ on, attrs }">
          <v-btn icon dark v-bind="attrs" v-on="on">
            <v-avatar>
              <img v-if="user && userPhoto" :src="userPhoto" alt="FOTO USUARIO" />
              <img alt="usuario" v-else :src="require('../img/default-user-image.png')">
            </v-avatar>
          </v-btn>
        </template>
        <v-card>
          <v-list>
            <v-list-item v-if="user">
              <v-list-item-avatar >
                <img v-if="userPhoto" :src="userPhoto" alt="FOTO USUARIO" />
                <img alt="usuario" v-else :src="require('../img/default-user-image.png')">
              </v-list-item-avatar>
              <v-list-item-content>
                <v-list-item-title>{{ user.name }}</v-list-item-title>
                <v-list-item-subtitle>{{ user.userName }}</v-list-item-subtitle>
              </v-list-item-content>
            </v-list-item>
          </v-list>
          <v-divider></v-divider>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn @click="logout" dark color="#1E286C">SALIR</v-btn>
          </v-card-actions>
        </v-card>
      </v-menu>
    </v-app-bar>

    <v-footer dark app color="#1E286C">
      &#64; INTERNATIONAL SOS 2021
    </v-footer>
  </v-app>
</template>

<script>
import ModulosRol from "./ModulosRol";
import { mapState } from "vuex"

export default {
  components: {
    ModulosRol
  },
  data: () => ({
    drawer: null,
    fav: true,
    menu: false,
    profile: null,
    isLoading: true,
  }),
  async created() {
    if (!this.$msal.isAuthenticated()) {
      return this.$msal.signIn();
    }
    const user = {
      ...this.msal.user,
    }
    user.roles = this.msal.user.idTokenClaims.roles
    this.$store.commit('currentUser/SET_USER', user);
    try {
      const res = await this.$msal.acquireToken()
      await this.$store.dispatch('currentUser/login', await res.accessToken)
    } catch (e) {
      console.error(e)
    }
  },
  methods: {
    logout() {
      this.$store.dispatch('currentUser/logout')
      this.$msal.signOut()
    }
  },
  computed: {
    ...mapState('currentUser',['user']),
    ...mapState(['snackbar']),
    userPhoto() {
      let photo = null;
      if (this.msal.isAuthenticated && this.msal.graph && this.msal.graph.photo) {
        const imageElm = document.createElement("img");
        imageElm.src = URL.createObjectURL(this.msal.graph.photo);
        photo = imageElm.src;
      }
      return photo;
    },
  },
};
</script>
<template>
  <div>
    <v-dialog v-model="dialog_curva" persistent max-width="800px">
      <v-card>
        <v-card-title>CURVA PCR</v-card-title>
        <v-divider></v-divider>
        <v-img :src="`api/v1/fotoPcr/${curva.path}`" contain></v-img>
        <v-divider></v-divider>
        <v-card-text>
          <p class="subtitle-1">COMENTARIO:</p>
          {{ curva.detalle }}
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="primary" text @click="close">CERRAR</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import { mapState } from 'vuex'
export default {
  props: ['curva'],
  methods: {
    close() {
      this.$store.commit('is/SHOW_DIALOG_CURVA', false)
    },
  },
  computed: {
    ...mapState('is',['dialog_curva'])
  }
}
</script>
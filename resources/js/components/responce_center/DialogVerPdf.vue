<template>
  <div>
    <v-dialog v-model="dialog" fullscreen max-width="700px" hide-overlay transition="dialog-bottom-transition">
      <v-toolbar dark color="primary">
        <v-btn icon dark @click="close">
          <v-icon>mdi-close</v-icon>
        </v-btn>
        <v-toolbar-title>Achivos PDF</v-toolbar-title>
      </v-toolbar>
      <v-card>
        <pdf v-for="file in files" :key="file.id" :src="`api/v1/evidencia/pdf/${file.path}`"></pdf>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import pdf from 'vue-pdf'
export default {
  components: {
    pdf
  },
  props: ['files'],
  computed: {
    dialog() {
      return this.$store.state.rc.dialog.pdf
    }
  },
  methods: {
    close() {
      this.$store.commit('rc/SHOW_DIALOG_PDF', false)
    }
  }
}
</script>
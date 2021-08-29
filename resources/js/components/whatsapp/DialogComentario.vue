<template>
  <div>
    <v-dialog v-model="showDialog" width="500px" scrollable>
      <v-card>
        <v-card-title>DETALLE CURVA PCR</v-card-title>
        <v-divider></v-divider>
        <v-card-text>
          {{ comment }}
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text color="normal" @click="close">CERRAR</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
export default {
  props: {
    comment: String,
    showDialog: {
      type: Boolean,
      default: false,
    }
  },
  methods: {
    close() {
      this.$emit('close-dialog')
    }
  },
  computed: {
  }
}
</script>

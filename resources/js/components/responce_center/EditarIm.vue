<template>
  <div>
      <v-card outlined v-if="im">
        <v-card-title>INDICACIONES MEDICAS</v-card-title>
        <v-divider></v-divider>
        <v-card-text>
          <p>
            {{ im.descr_espvalorada }}
          </p>
          <v-card width="400px" elevation="5">
            <v-img :src="im.firma_doctor"></v-img>
            <v-divider></v-divider>
            <v-card-subtitle>
              <div class="d-flex">
                FIRMA DOCTOR
                <v-spacer></v-spacer>
                <v-btn color="info" dark @click="dialog = true"><v-icon dark>mdi-pencil</v-icon></v-btn>
              </div>
            </v-card-subtitle>
          </v-card>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text color="normal" to="/rc">ATRAS</v-btn>
          <v-btn color="primary" @click="guardar" :disabled="!im.firma_doctor">GUARDAR</v-btn>
        </v-card-actions>
      </v-card>

      <v-dialog max-width="450px" v-model="dialog" persistent>
        <v-card>
          <v-card-title>AGREGAR FIRMA</v-card-title>
          <v-card-subtitle>Por favor firme en el recuadro</v-card-subtitle>
          <v-divider class="mb-5"></v-divider>
          <v-card-text style="height: 250px;">
            <vue-signature id="signature" ref="signature" w="100%" h="100%" :sigOption="option"></vue-signature>
          </v-card-text>
          <v-divider></v-divider>
          <v-card-actions>
            <v-btn @click="dialog = false" text color="normal">CANCELAR</v-btn>
            <v-spacer></v-spacer>
            <v-btn @click="clear" color="normal">BORRAR</v-btn>
            <v-btn :disabled="!!($refs.signature && $refs.signature.isEmpty())" @click="save" color="info">OK</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
  </div>
</template>

<script>
export default {
  data() {
    return {
      menu: false,
      option:{
        penColor:"rgb(0, 0, 0)",
        backgroundColor:"rgb(255,255,255)"
      },
      dialog: false,
    }
  },
  computed: {
    im() {
      return this.$store.state.rc.indicaciones_medicas
    },
  },
  methods: {
    guardar() {
      //this.$refs.observer.validate().then(isValid => {
        //if (!isValid) return
        this.$store.dispatch('rc/updateIndicacionesMedicas', this.im)
      //})
    },
    clear(){
      this.$refs.signature.clear();
    },
    save(){
      this.im.firma_doctor = this.$refs.signature.save('image/jpeg');
      this.dialog = false;
    },
  },
  created() {
    this.$store.dispatch('rc/showIndicacionesMedicas', this.$route.params.idEvidencia)
  }
}
</script>

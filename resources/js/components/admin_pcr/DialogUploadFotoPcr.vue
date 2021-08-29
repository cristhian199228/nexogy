<template>
  <div>
    <v-dialog v-model="dialog.foto" max-width="600px" persistent>
      <v-card>
        <v-card-title>SUBIR FOTO</v-card-title>
        <v-divider></v-divider>
        <v-card-text>
          <validation-observer ref="observer" v-slot="{validate}">
            <v-file-input show-size accept="image/*" ref="photo" @change="seleccionarFoto" label="File input"></v-file-input>
            <validation-provider name="comment" rules="required" v-slot="{errors}">
              <v-text-field v-model="detalle" :error-messages="errors"
                            label="Comentario" prepend-icon="mdi-comment-outline"></v-text-field>
            </validation-provider>
          </validation-observer>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text color="normal" @click="close">CANCELAR</v-btn>
          <v-btn color="primary" :loading="loading" @click="subirFoto"><v-icon left dark>mdi-cloud-upload</v-icon>Subir</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import {mapState, mapActions, mapMutations} from 'vuex';
import {required, image } from "vee-validate/dist/rules";
import { extend, ValidationObserver, ValidationProvider, setInteractionMode } from 'vee-validate'

setInteractionMode("eager");
extend("required", {
  ...required,
});
extend("image", {
  ...required,
});

export default {
  components: {
    ValidationProvider,
    ValidationObserver
  },
  props: ['id_pcr'],
  data(){
    return{
      detalle: null,
      loading: false,
      archivo: undefined,
    }
  },
  computed: {
    ...mapState('admin_pcr',['dialog','data']),
  },
  methods: {
    ...mapActions('admin_pcr',['uploadPhoto', 'getFichas']),
    ...mapMutations('admin_pcr',['SHOW_DIALOG_UPLOAD_FOTO_MUESTRA']),
    close() {
      this.SHOW_DIALOG_UPLOAD_FOTO_MUESTRA(false);
      this.reset();
    },
    seleccionarFoto(file){
      this.archivo = file;
    },
    subirFoto(){
      const snackbar = {};
      if (this.archivo){
        this.$refs.observer.validate().then(async isValid => {
          if (isValid) {
            const params = {
              id_pcr: this.id_pcr,
              photo: this.archivo,
              detalle: this.detalle,
            };
            this.loading = true;
            try {
              await this.uploadPhoto(params);
              this.loading = false;
              snackbar.show = true;
              snackbar.color = "success";
              snackbar.message = "Se subió la foto correctamente";
              this.$store.commit('SHOW_SNACKBAR', snackbar);
              this.SHOW_DIALOG_UPLOAD_FOTO_MUESTRA(false);
              this.reset();
              this.getFichas(this.$store.state.admin_pcr.data.current_page);
            } catch (e) {
              console.error(e.response)
              this.loading = false;
              snackbar.show = true;
              snackbar.color = "error";
              snackbar.message = "Ocurrió un error";
              this.$store.commit('SHOW_SNACKBAR', snackbar);
            }
          }
        })
      } else {
        snackbar.show = true;
        snackbar.color = "error";
        snackbar.message = "Por favor selecciona una foto";
        this.$store.commit('SHOW_SNACKBAR', snackbar);
      }
    },
    reset(){
      this.detalle = null;
      this.archivo = undefined;
      if (this.$refs.photo) this.$refs.photo.reset();
    }
  }
}
</script>
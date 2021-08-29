<template>
    <div>
        <v-dialog max-width="600px" persistent v-model="dialog.foto">
            <v-card>
                <v-card-title>SUBIR FOTO {{ title }}</v-card-title>
                <v-card-text>
                    <input type="file" capture="camera" ref="photo" @change="seleccionarFoto" :accept="store === 'rc' ? 'image/*,.pdf' : 'image/*'"  />
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn text color="normal" @click="close">ATRAS</v-btn>
                    <v-btn color="primary" :loading="loading" @click="subirFoto"><v-icon left dark>mdi-cloud-upload</v-icon>Subir</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
import { mapState } from 'vuex'
export default {
    props: ['title','store','resource'],
    methods: {
        close() {
            this.$store.commit('SHOW_DIALOG_UPLOAD_FOTO',false);
        },
        seleccionarFoto(e){
            this.$store.commit('SET_PHOTO', e.target.files[0]);
        },
        subirFoto(){
            if(this.photo) {
                this.$store.dispatch(this.store + '/upload' + this.resource);
            } else {
                const snackbar = {
                    show: true,
                    message: "Por favor seleccione una foto!",
                    color: "error"
                };
                this.$store.commit('SHOW_SNACKBAR', snackbar);
            }
        },
    },
    computed: {
        ...mapState(['dialog','loading','photo'])
    },
}
</script>

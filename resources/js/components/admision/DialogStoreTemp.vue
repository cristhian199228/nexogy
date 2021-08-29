<template>
    <div>
        <v-dialog scrollable persistent max-width="600px" v-model="dialog.upload.temp">
            <v-card>
                <v-card-title>GUARDAR TEMPERATURA</v-card-title>
                <v-card-text>
                    <v-row no-gutters justify="center">
                        <v-col class="text-center mb-2" cols="3">
                            <v-btn outlined color="blue darken-4" @click="temp.ent++" :disabled="temp.ent >= 42"><v-icon>mdi-plus</v-icon></v-btn>
                        </v-col>
                        <v-col class="text-center" cols="1"></v-col>
                        <v-col class="text-center" cols="3">
                            <v-btn outlined color="blue darken-4" @click="temp.dec++" :disabled="temp.dec >= 9"><v-icon>mdi-plus</v-icon></v-btn>
                        </v-col>
                    </v-row>
                    <v-row no-gutters justify="center">
                        <v-col class="text-center" cols="3">
                            <h1 class="display-1">{{temp.ent}}</h1>
                        </v-col>
                        <v-col class="text-center" cols="1">
                            <h1 class="display-1">.</h1>
                        </v-col>
                        <v-col class="text-center" cols="3">
                            <h1 class="display-1">{{temp.dec}}</h1>
                        </v-col>
                    </v-row>
                    <v-row no-gutters justify="center">
                        <v-col class="text-center" cols="3">
                            <v-btn outlined color="blue darken-4" @click="temp.ent--" :disabled="temp.ent <= 30"><v-icon>mdi-minus</v-icon></v-btn>
                        </v-col>
                        <v-col class="text-center" cols="1"></v-col>
                        <v-col class="text-center" cols="3">
                            <v-btn outlined color="blue darken-4" @click="temp.dec--" :disabled="temp.dec <= 0"><v-icon>mdi-minus</v-icon></v-btn>
                        </v-col>
                    </v-row>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn text color="normal" @click="close">ATRAS</v-btn>
                    <v-btn color="primary" :loading="loading" @click="guardarTemp">GUARDAR</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
import { mapState, mapActions, mapMutations } from 'vuex'
export default {
    data() {
        return {
            temp: {
                ent: 36,
                dec: 6,
            }
        }
    },
    computed: {
        ...mapState('admision',['dialog','loading'])
    },
    methods: {
        ...mapMutations('admision',['SHOW_DIALOG_STORE_TEMP']),
        ...mapActions('admision',['storeTemp']),
        guardarTemp() {
            this.storeTemp(Number(this.temp.ent + '.' + this.temp.dec));
        },
        close() {
            this.SHOW_DIALOG_STORE_TEMP(false);
        },
    },
}
</script>

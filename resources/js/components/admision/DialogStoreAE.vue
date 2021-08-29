<template>
    <div>
        <v-dialog scrollable persistent max-width="600px" v-model="dialog.upload.ae">
            <v-card>
                <v-card-title>ANT. EPIDEMIOLÓGICOS/PATOLÓGICOS</v-card-title>
                <v-card-text>
                    <v-switch hide-details v-model="form.dias_viaje" label="Durante los últimos 14 días ha estado residiendo o ha
                    viajado desde cualquier país o área de Alto Riesgo"></v-switch>
                    <template v-if="form.dias_viaje">
                        <v-text-field prepend-icon="mdi-car" outlined dense v-if="form.dias_viaje" v-model="form.medio_transporte" label="Medio de transporte"></v-text-field>
                        <v-menu
                            ref="menu"
                            v-model="form.menu_dias_viaje"
                            :close-on-content-click="false"
                            transition="scale-transition"
                            offset-y
                            min-width="290px"
                        >
                            <template v-slot:activator="{ on, attrs }">
                                <v-text-field
                                    v-model="form.fecha_llegada"
                                    label="Fecha de llegada"
                                    prepend-icon="mdi-calendar"
                                    readonly
                                    v-bind="attrs"
                                    v-on="on"
                                    outlined
                                    dense
                                ></v-text-field>
                            </template>
                            <v-date-picker
                                ref="picker"
                                v-model="form.fecha_llegada"
                                :max="new Date().toISOString().substr(0, 10)"
                                min="2020-01-01"
                                @change="save"
                            ></v-date-picker>
                        </v-menu>
                        <v-text-field prepend-icon="mdi-map-marker" hide-details v-model="form.paises_visitados" dense outlined label="Enumere todos los países y lugares donde ha residido o
                        viajado en los últimos 14 días (incluso si no está afectado por COVID-19)">
                        </v-text-field>
                    </template>
                    <v-switch hide-details v-model="form.contacto_cercano" label="¿Tuvo contacto cercano (compartiendo alojamiento o
                    proporcionando cuidado)"></v-switch>
                    <template v-if="form.contacto_cercano">
                        <v-menu
                            ref="menu"
                            v-model="form.menu_contacto_cercano"
                            :close-on-content-click="false"
                            transition="scale-transition"
                            offset-y
                            min-width="290px"
                        >
                            <template v-slot:activator="{ on, attrs }">
                                <v-text-field
                                    v-model="form.fecha_contacto"
                                    label="Fecha de contacto"
                                    prepend-icon="mdi-calendar"
                                    readonly
                                    v-bind="attrs"
                                    v-on="on"
                                    outlined
                                    dense
                                    hide-details
                                ></v-text-field>
                            </template>
                            <v-date-picker
                                ref="picker"
                                v-model="form.fecha_contacto"
                                :max="new Date().toISOString().substr(0, 10)"
                                min="2020-01-01"
                                @change="save"
                            ></v-date-picker>
                        </v-menu>
                    </template>
                    <v-switch hide-details v-model="form.conv_covid" label="¿Pasó tiempo en la distancia de conversación con una persona
                        que tiene o está bajo investigación por COVID-19?">
                    </v-switch>
                    <v-switch hide-details v-model="form.condicion_existente" label="TIENE USTED ALGUNA CONDICION EXISTENTE QUE DEBILITE SU
                        SISTEMA INMUNE">
                    </v-switch>
                    <v-text-field hide-details v-if="form.condicion_existente" v-model="form.condicion_existente_texto" dense outlined label="Describa aquí"></v-text-field>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn text color="normal" @click="close">ATRAS</v-btn>
                    <v-btn color="primary" @click="guardarAE">GUARDAR</v-btn>
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
            form: {
                dias_viaje: 0,
                medio_transporte: "",
                menu_dias_viaje: false,
                fecha_llegada: null,
                contacto_cercano: 0,
                menu_contacto_cercano: false,
                fecha_contacto: null,
                conv_covid: 0,
                paises_visitados: "",
                condicion_existente: 0,
                condicion_existente_texto: "",
            }
        }
    },
    computed: {
        ...mapState('admision',['dialog','loading'])
    },
    methods: {
        ...mapMutations('admision',['SHOW_DIALOG_STORE_AE']),
        ...mapActions('admision',['storeAE']),
        guardarAE() {
            this.storeAE(this.form);
        },
        close() {
            this.SHOW_DIALOG_STORE_AE(false);
        },
        save(date) {
            this.$refs.menu.save(date)
        },
    },
    watch: {
        menu (val) {
            val && setTimeout(() => (this.$refs.picker.activePicker = 'YEAR'))
        },
    }


}
</script>

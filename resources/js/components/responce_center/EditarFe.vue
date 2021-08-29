<template>
    <div>
        <v-card outlined>
            <validation-observer ref="observer" v-slot="{invalid}">
            <v-card-title>FICHA DE INFORMACIÓN CON DATOS EPIDEMIOLÓGICOS</v-card-title>
            <v-divider></v-divider>
            <v-card-text>
                <v-row>
                    <v-col cols="12" lg="8" md="8" sm="8"  xs="12">
                        <v-text-field prepend-icon="mdi-account" dense disabled label="NOMBRE COMPLETO" v-model="paciente.nom_completo"></v-text-field>
                    </v-col>
                    <v-col cols="12" lg="4" md="4" sm="4" xs="12">
                        <v-text-field type="number" prepend-icon="mdi-card-account-details" dense disabled label="DNI" v-model="paciente.numero_documento"></v-text-field>
                    </v-col>
                </v-row>
                <v-row>
                    <v-col cols="12">
                        <v-text-field prepend-icon="mdi-map-marker" dense clearable label="DIRECCION COMPLETA" v-model="paciente.direccion"></v-text-field>
                    </v-col>
                </v-row>
                <v-row>
                    <v-col cols="12" lg="6" md="6" sm="6"  xs="12">
                        <v-text-field type="number" prepend-icon="mdi-phone" dense clearable label="CELULAR" v-model="paciente.celular"></v-text-field>
                    </v-col>
                    <v-col cols="12" lg="6" md="6" sm="6" xs="12">
                        <v-text-field prepend-icon="mdi-account-hard-hat" dense clearable label="REGISTRO" v-model="paciente.nro_registro"></v-text-field>
                    </v-col>
                </v-row>
                <v-row>
                    <v-col cols="12">
                        <v-text-field prepend-icon="mdi-domain" dense disabled label="EMPRESA" v-model="paciente.empresa.descripcion"></v-text-field>
                    </v-col>
                </v-row>
                <v-row>
                    <v-col cols="12" lg="8" md="8" sm="8"  xs="12">
                        <v-text-field prepend-icon="mdi-account" dense clearable label="NOMBRE SUPERVISOR" v-model="fe.nombre_supervisor"></v-text-field>
                    </v-col>
                    <v-col cols="12" lg="4" md="4" sm="4" xs="12">
                        <validation-provider v-slot="{errors}" name="phone" rules="min:9|integer">
                            <v-text-field type="number" prepend-icon="mdi-phone" dense clearable label="CELULAR SUPERVISOR" :error-messages="errors"
                                          v-model="fe.celular_supervisor"></v-text-field>
                        </validation-provider>
                    </v-col>
                </v-row>
                <v-divider class="my-2"></v-divider>
                <h5>PERIODOS DE ESTANCIA EN MINA</h5>
                <br>
                <v-row>
                    <v-col cols="12" lg="6" md="6" sm="6" xs="12">
                        <v-menu
                            ref="menu2"
                            v-model="menu2"
                            :close-on-content-click="false"
                            :return-value.sync="p1"
                            transition="scale-transition"
                            offset-y
                            min-width="auto"
                        >
                            <template v-slot:activator="{ on, attrs }">
                                <validation-provider v-slot="{errors}" name="periodo" rules="length:2">
                                    <v-text-field
                                        v-model="p1"
                                        label="ULTIMO PERIODO"
                                        prepend-icon="mdi-calendar"
                                        readonly
                                        v-bind="attrs"
                                        v-on="on"
                                        dense
                                        :error-messages="errors"
                                        messages="Seleccione un rango de fechas (Ej: 2021-02-11, 2021-02-15)"
                                    ></v-text-field>
                                </validation-provider>
                            </template>
                            <v-date-picker range v-model="p1" no-title scrollable>
                                <v-spacer></v-spacer>
                                <v-btn text color="primary" @click="menu2 = false">Cancel</v-btn>
                                <v-btn text color="primary" @click="$refs.menu2.save(p1)">OK</v-btn>
                            </v-date-picker>
                        </v-menu>
                    </v-col>
                </v-row>
                <v-divider></v-divider>
                <v-switch v-model="fe.prueba_positiva" hide-details
                          inset dense label="¿Salió POSITIVO en una prueba Serológica (Rápida) o Molecular (Hisopado) previa a la fecha?"></v-switch>
                <v-switch v-model="fe.prueba_cv" hide-details
                          inset dense @change="pruebaCv" label="¿La prueba POSITIVA fue en el Tamizaje de Cerro verde?"></v-switch>
                    <validation-provider v-slot="{errors}" name="doctype" rules="" vid="otro">
                        <v-switch v-model="fe.prueba_otro"
                                  inset dense @change="pruebaOtro" label="¿La prueba POSITIVA fue en alguna otra institución o laboratorio?"></v-switch>
                    </validation-provider>
                <template v-if="fe.prueba_otro">
                    <v-row>
                        <v-col cols="12" lg="6" md="6" sm="6" xs="12">
                            <validation-provider v-slot="{errors}" name="tipo de prueba" rules="required_if:otro,true,1">
                                <v-select v-model="fe.prueba_otro_tipo" label="TIPO DE PRUEBA" :error-messages="errors"
                                          dense :items="pruebas" item-text="text" item-value="id"></v-select>
                            </validation-provider>
                        </v-col>
                        <v-col cols="12" lg="6" md="6" sm="6" xs="12">
                            <v-menu
                                ref="menu4"
                                v-model="menu4"
                                :close-on-content-click="false"
                                :return-value.sync="fe.prueba_otro_fecha"
                                transition="scale-transition"
                                offset-y
                                min-width="auto"
                            >
                                <template v-slot:activator="{ on, attrs }">
                                    <validation-provider v-slot="{errors}" name="fecha" rules="required_if:otro,true,1">
                                        <v-text-field
                                            v-model="fe.prueba_otro_fecha"
                                            label="FECHA DE REALIZACIÓN"
                                            prepend-icon="mdi-calendar"
                                            readonly
                                            v-bind="attrs"
                                            v-on="on"
                                            dense
                                            :error-messages="errors"
                                        ></v-text-field>
                                    </validation-provider>
                                </template>
                                <v-date-picker v-model="fe.prueba_otro_fecha" no-title scrollable>
                                    <v-spacer></v-spacer>
                                    <v-btn text color="primary" @click="menu4 = false">Cancel</v-btn>
                                    <v-btn text color="primary" @click="$refs.menu4.save(fe.prueba_otro_fecha)">OK</v-btn>
                                </v-date-picker>
                            </v-menu>
                        </v-col>
                    </v-row>
                    <v-row>
                        <v-col cols="12" lg="6" md="6" sm="6" xs="12">
                            <validation-provider v-slot="{errors}" name="resultado" rules="required_if:otro,true,1">
                                <v-select v-model="fe.prueba_otro_resultado" label="RESULTADO" :error-messages="errors"
                                          dense :items="fe.prueba_otro_tipo === 2 ? resultados_otros_prs : resultados_otros_pcr"
                                          item-text="text" item-value="id"></v-select>
                            </validation-provider>
                        </v-col>
                        <v-col cols="12" lg="6" md="6" sm="6" xs="12">
                            <validation-provider v-slot="{errors}" name="lugar" rules="required_if:otro,true,1">
                                <v-text-field prepend-icon="mdi-map-marker" dense :error-messages="errors"
                                              label="LUGAR DE REALIZACION" v-model="fe.prueba_otro_lugar"></v-text-field>
                            </validation-provider>
                        </v-col>
                    </v-row>
                </template>
                <v-row>
                    <v-col cols="12">
                        <v-text-field dense label="OBSERVACIONES" v-model="fe.observaciones"></v-text-field>
                    </v-col>
                </v-row>
                <v-divider></v-divider>
                <h5>LISTA DE CONTACOS DIRECTOS |
                    <v-tooltip bottom>
                        <template v-slot:activator="{ on, attrs }">
                            <v-btn icon @click="nuevoCD" color="primary" v-bind="attrs" v-on="on"><v-icon>mdi-plus-circle</v-icon></v-btn>
                        </template>
                        <span>Agregar contacto directo</span>
                    </v-tooltip>
                </h5>
                <template v-if="contactos.length > 0">
                    <v-simple-table dense fixed-header>
                        <template v-slot:default>
                            <thead>
                            <tr>
                                <th class="text-left">N°</th>
                                <th class="text-left">Nombres</th>
                                <th class="text-left">Celular</th>
                                <th class="text-left">Cargo</th>
                                <th class="text-left">Detalle</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="cd in contactos" :key="cd.id">
                                <td>{{ cd.contador }}</td>
                                <td>{{ cd.nombres }}</td>
                                <td>{{ cd.celular }}</td>
                                <td>{{ cd.cargo }}</td>
                                <td>{{ cd.detalle }}</td>
                            </tr>
                            </tbody>
                        </template>
                    </v-simple-table>
                </template>
                <template v-else>
                    NO SE HAN INGRESADO CONTACTOS DIRECTOS
                </template>
            </v-card-text>
            <v-divider></v-divider>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn text color="normal" @click="regresar">ATRAS</v-btn>
                <v-btn :disabled="invalid" color="primary" @click="updateFe">GUARDAR</v-btn>
            </v-card-actions>
            </validation-observer>
        </v-card>
        <NuevoContacto />
    </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex';
import { required, required_if, min, integer, length} from "vee-validate/dist/rules";
import { extend, ValidationObserver, ValidationProvider, setInteractionMode } from 'vee-validate'
import NuevoContacto from "./NuevoContacto";

setInteractionMode("eager");
extend("required", {
    ...required,
    message: "{_field_} can not be empty"
});
extend("min", {
    ...min,
    message: "{_field_} may not be less than {length} characters"
});
extend("integer", {
    ...integer,
});
extend("required_if", {
    ...required_if,
    message: "{_field_} can not be empty"
});
extend("length", {
    ...length,
});

export default {
    components: {
        NuevoContacto,
        ValidationObserver,
        ValidationProvider,
    },
    data() {
        return {
            p1: [],
            menu1: false,
            menu2: false,
            menu3: false,
            menu4: false,
            resultados: [
                { id: 0, text: "NEGATIVO" },
                { id: 1, text: "POSITIVO" }
            ],
            pruebas: [
                { id: 1, text: 'PRUEBA MOLECULAR(hisopado)'},
                { id: 2, text: 'PRUEBA SEROLÓGICA(prueba rápida)'},
                { id: 3, text: 'PRUEBA ANTIGENA'},
            ],
            resultados_otros_prs: [
                { id: 2, text: "IGG" },
                { id: 3, text: "IGM" },
                { id: 4, text: "IGM/IGG" }
            ],
            resultados_otros_pcr: [
                { id: 0, text: "NEGATIVO" },
                { id: 1, text: "POSITIVO" },
            ]
        }
    },
    methods: {
        regresar () {
            this.$router.push('/rc');
            this.$store.commit('rc/SET_ID_EV', null);
            this.$store.commit('rc/SET_ID_FE', null);
        },
        updateFe () {
            this.$refs.observer.validate().then(isValid => {
                if (isValid) {
                    this.$store.dispatch('rc/updateFe', {
                        pac: this.paciente,
                        fe: this.fe,
                        p1: this.p1,
                    });
                }
            })
        },
        pruebaCv() {
            if (this.fe.prueba_cv) {
                this.fe.prueba_otro = false;
                this.fe.prueba_otro_tipo = null;
                this.fe.prueba_otro_fecha = null;
                this.fe.prueba_otro_resultado = null;
                this.fe.prueba_otro_lugar = null;
            }
        },
        pruebaOtro() {
            if (this.fe.prueba_otro) {
                this.fe.prueba_cv = false;
            } else {
                this.fe.prueba_otro = false;
                this.fe.prueba_otro_tipo = null;
                this.fe.prueba_otro_fecha = null;
                this.fe.prueba_otro_resultado = null;
                this.fe.prueba_otro_lugar = null;
            }
        },
        nuevoCD() {
            this.$store.commit('rc/SHOW_DIALOG_CONTACTO', true);
        },
        cargarFechas () {
            if (this.fe.p1_fecha_inicio && this.fe.p1_fecha_fin) {
                this.p1.push(this.fe.p1_fecha_inicio);
                this.p1.push(this.fe.p1_fecha_fin);
            }
        }
    },
    computed: {
        ...mapGetters('rc',['getEvidenciaById']),
        ...mapState('rc',['contactos']),
        fe() {
            return this.getEvidenciaById.ficha_ep;
        },
        paciente() {
            return this.getEvidenciaById.paciente;
        },
    },
    created() {
        this.$store.dispatch('rc/getContactos');
        this.cargarFechas();
    }
}
</script>

<template>
    <div>
        <v-card outlined>
            <v-card-title>FORMATO CENTRO DE AISLAMIENTO</v-card-title>
            <v-divider></v-divider>
            <v-card-text>
                YO <b>{{ paciente.nom_completo }}</b> TRABAJADOR DE LA EMPRESA SOCIEDAD MINERA CERRO VERDE S.A.A.
                CON REGISTRO N° <b>{{ paciente.nro_registro }}</b>  Y CON DNI N° <b>{{ paciente.numero_documento }}</b>
                HE SIDO INFORMADO SOBRE EL CENTRO DE AISLAMIENTO, EL CUAL SE ENCUENTRA
                DISPONIBLE PARA TODOS LOS TRABAJADORES DE SMCV Y EL INGRESO AL MISMO ES DE FORMA VOLUNTARIA.
                <br>
                POR LO TANTO:
                <v-chip-group v-model="fc.estado" mandatory active-class="primary--text">
                    <v-chip v-for="tag in tags" :key="tag">{{ tag }}</v-chip>
                </v-chip-group>
                HACER USO DEL CENTRO DE LAS INSTALACIONES DEL CENTRO DE AISLAMIENTO ANTES MENCIONADO.
                <br>
                <br>
            </v-card-text>
            <v-divider></v-divider>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn text color="normal" @click="regresar">ATRAS</v-btn>
                <v-btn color="primary" @click="updateFc">GUARDAR</v-btn>
            </v-card-actions>
        </v-card>
    </div>
</template>

<script>
import {mapGetters} from "vuex";

export default {
    data () {
        return {
            tags: ["NO ACEPTO" , "ACEPTO"],
        }
    },
    methods: {
        regresar () {
            this.$router.push('/rc');
            this.$store.commit('rc/SET_ID_EV', null);
            this.$store.commit('rc/SET_ID_FC', null);
        },
        updateFc () {
            this.$store.dispatch('rc/updateFc', this.fc);
        },
    },
    computed: {
        ...mapGetters('rc', ['getEvidenciaById']),
        fc() {
            return this.getEvidenciaById.ficha_cam;
        },
        paciente() {
            return this.getEvidenciaById.paciente;
        },
    },
    created() {

    }
}
</script>

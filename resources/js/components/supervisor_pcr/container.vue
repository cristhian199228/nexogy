<template>
    <div>
        <viewer :images="images" @inited="inited" class="viewer" ref="viewer">
            <img v-for="(src,i) in images" :src="src" :key="i" class="image" />
        </viewer>
        <v-card outlined>
            <buscador-pcr-salud :store="modulo" title="SUPERVISOR PCR" />
            <v-data-table :headers="headers" :items="fichas" :loading="loading_table" :items-per-page="15" hide-default-footer>
                <template v-slot:item.contador="{item}">
                    <small>{{ item.contador }}</small>
                </template>
                <template v-slot:item.nom_completo="{item}">
                    <template v-if="item.paciente_isos">
                        <small>{{ item.paciente_isos.nom_completo }}</small>
                    </template>
                </template>
                <template v-slot:item.dni="{item}">
                    <template v-if="item.paciente_isos">
                        <small>{{ item.paciente_isos.numero_documento }}</small>
                    </template>
                </template>
                <template v-slot:item.tid="{item}">
                    <template v-if="item.pcr_prueba_molecular && item.pcr_prueba_molecular.pcr_envio_munoz">
                        <small>{{ item.pcr_prueba_molecular.pcr_envio_munoz.transaction_id }}</small>
                    </template>
                </template>
                <template v-slot:item.sede="{item}">
                    <template v-if="item.estacion && item.estacion.sede">
                        <small>{{ item.estacion.sede.descripcion }}</small>
                    </template>
                </template>
                <template v-slot:item.est="{item}">
                    <template v-if="item.estacion && item.estacion.sede">
                        <small>{{ item.estacion.nom_estacion }}</small>
                    </template>
                </template>
                <template v-slot:item.nom_empresa="{item}">
                    <small v-if="item.paciente_isos && item.paciente_isos.empresa">
                        {{ item.paciente_isos.empresa.descripcion }}
                    </small>
                </template>
                <template v-slot:item.rol="{item}">
                    <template v-if="item.rol">
                        <small v-if="item.rol === 1">ACUARTELADO</small>
                        <small v-else-if="item.rol === 2">ITINERANTE</small>
                        <small v-else-if="item.rol === 3">NO APLICA</small>
                    </template>
                </template>
                <template v-slot:item.turno="{item}">
                    <template v-if="item.turno">
                        <v-icon v-if="item.turno === 1">mdi-arrow-up-bold</v-icon>
                        <v-icon v-else-if="item.turno === 2">mdi-arrow-down-bold</v-icon>
                        <small v-else-if="item.turno === 3">NO APLICA</small>
                    </template>
                </template>
                <template v-slot:item.fin="{item}">
                    <v-chip small dark color="indigo" v-if="item.pcr_prueba_molecular.hora_fin">
                        {{ item.pcr_prueba_molecular.hora_fin.substring(0,5) }}
                    </v-chip>
                </template>
                <template v-slot:item.foto="{item}">
                    <template v-if="item.pcr_prueba_molecular &&
                        item.pcr_prueba_molecular.ficha_investigacion &&
                        item.pcr_prueba_molecular.ficha_investigacion.ficha_inv_foto &&
                        item.pcr_prueba_molecular.ficha_investigacion.ficha_inv_foto.path &&
                        item.pcr_prueba_molecular.ficha_investigacion.ficha_inv_foto.path2"
                    >
                        <v-btn icon small @click="verFoto(item.pcr_prueba_molecular.ficha_investigacion.ficha_inv_foto)"><v-icon small>mdi-eye</v-icon></v-btn>
                    </template>
                </template>
                <template v-slot:item.res="{item}">
                    <template v-if="item.pcr_prueba_molecular && item.pcr_prueba_molecular.resultado !== null">
                        <v-chip small dark color="green darken-2" v-if="item.pcr_prueba_molecular.resultado === 0">NEG</v-chip>
                        <v-chip small dark color="red darken-2" v-else-if="item.pcr_prueba_molecular.resultado === 1">POS</v-chip>
                        <v-chip small dark color="orange darken-2" v-else-if="item.pcr_prueba_molecular.resultado === 2">ANULADO</v-chip>
                    </template>
                </template>
            </v-data-table>
            <update-fab-button :store="modulo" />
            <paginate :store="modulo" />
            <DialogReporte :store="modulo" />
        </v-card>
    </div>
</template>

<script>
import { mapState } from "vuex";
import Viewer from "v-viewer/src/component.vue"
import paginate from "../paginate";
import DialogReporte from "../DialogReporte";
import buscadorPcrSalud from "../buscadorPcrSalud";
import UpdateFabButton from "../UpdateFabButton";

export default {
    components: {
        Viewer,
        paginate,
        DialogReporte,
        buscadorPcrSalud,
        UpdateFabButton
    },
    data() {
        return {
            modulo: "super_pcr",
            headers: [
                { text: 'N°', align: 'start', value: 'contador', sortable: false  },
                { text: 'Nombres', value: 'nom_completo', sortable: false },
                { text: 'DNI', value: 'dni', sortable: false },
                { text: 'T.ID', value: 'tid', sortable: false },
                { text: 'Sede', value: 'sede', sortable: false },
                { text: 'Estacion', value: 'est', sortable: false },
                { text: 'Empresa', value: 'nom_empresa', sortable: false },
                { text: 'Rol', value: 'rol', sortable: false },
                { text: 'Turno', value: 'turno', sortable: false },
                { text: 'Fin.', value: 'fin', sortable: false },
                { text: 'Foto', value: 'foto', sortable: false },
                { text: 'Resultado', value: 'res', sortable: false },
            ],
            images: [],
        }
    },
    methods: {
        verFoto(foto_inv){
            this.images = [];
            this.images.push("/api/v1/fi/" + foto_inv.path);
            this.images.push("/api/v1/fi2/" + foto_inv.path2);
            this.$viewer.show();
        },
        inited (viewer) {
            this.$viewer = viewer
        },
    },
    computed: {
        ...mapState('super_pcr',['loading_table']),
        fichas: {
            get() {
                return this.$store.state.super_pcr.data.data;
            }
        },
    },
    created() {
        this.$store.dispatch('super_pcr/getFichas', 1);
    }
}
</script>

<style>
.image{
    display: none;
}
</style>

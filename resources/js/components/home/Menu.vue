<template>
    <div>
        <v-row>
            <v-col v-for="(modulo,i) in modulos" :key="i" cols="12" lg="4" md="4" sm="6" xs="12">
                <v-card dark color="#385F73" :to="modulo.route">
                    <v-card-title class="headline">{{ modulo.title }}</v-card-title>
                    <v-img class="pa-5" contain src=""></v-img>
                </v-card>
            </v-col>
        </v-row>
    </div>
</template>

<script>
export default {
    data() {
        return {
            modulos: [
                { title: 'ADMISIÓN' , route: '/admision', src: '../../assets/operaciones.svg' },
                { title: 'CONTROLADOR PRS' , route: '/controlador', src: '../../assets/operaciones.svg' },
                { title: 'CONTROLADOR PCR' , route: '/pcr', src: '../../assets/operaciones.svg' },
                { title: 'SUPERVISOR PRS' , route: '/supervisor' ,src: '../../assets/supervisor_prs.svg' },
                { title: 'SUPERVISOR PCR' , route: '/supervisor_pcr', src: '../../assets/muestra.svg' },
                { title: 'DASHBOARD', route: '/dashboard', src: '../../assets/estadisticas.svg' },
                //{ title: 'MANTEMIENTO', route: '/mantenimiento', src: '../../assets/herramientas-y-utensilios.svg' },
                { title: 'SALUD', route: '/salud', src: '../../assets/salud.svg'}
            ],
        }
    },

}
</script>

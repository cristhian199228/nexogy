<template>
    <div>
        <v-card-title class="headline px-0" dense>
            {{ title }}
            <v-divider vertical class="mx-5"></v-divider>
            <v-chip label class="my-auto" v-if="nombreEstacion" @click="SHOW_DIALOG_ESTACION(true)" dark color="indigo">
                <v-avatar left><v-icon>mdi-map-marker</v-icon></v-avatar>{{ nombreEstacion }}
            </v-chip>
        </v-card-title>
        <v-divider class="mt-0"></v-divider>
    </div>
</template>

<script>
import { mapGetters, mapMutations } from 'vuex'
export default {
    props: ['title'],
    computed: {
        ...mapGetters(['nombreEstacion']),
    },
    methods: {
        ...mapMutations(['SHOW_DIALOG_ESTACION']),
    },
}
</script>

<template>
    <div>
        <v-sheet :color="`grey ${theme.isDark ? 'darken-2' : 'lighten-5'}`" class="pa-3">
            <v-row>
                <v-col v-for="n in 3" :key="n" cols="12" lg="4" md="4" sm="6" xs="12">
                    <v-skeleton-loader class="mx-auto" type="card"></v-skeleton-loader>
                </v-col>
                <v-col cols="12">
                    <v-skeleton-loader class="mx-auto" type="table"></v-skeleton-loader>
                </v-col>
            </v-row>
        </v-sheet>
    </div>
</template>

<script>
export default {
    inject: {
        theme: {
            default: { isDark: false },
        },
    },
}
</script>

<template>
    <div>
        <v-btn color="primary" fixed bottom right fab dark @click="updateView"><v-icon>mdi-update</v-icon></v-btn>
    </div>
</template>

<script>
export default {
    props: ['store'],
    methods: {
        updateView() {
            this.$store.dispatch( this.store + '/getFichas');
        },
    }
}
</script>

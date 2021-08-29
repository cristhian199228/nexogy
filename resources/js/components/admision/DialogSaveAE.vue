<template>
  <div>
    <v-dialog scrollable persistent max-width="600px" v-model="showDialog">
      <v-card>
        <v-card-title>ANT. EPIDEMIOLÓGICOS/PATOLÓGICOS</v-card-title>
        <v-divider></v-divider>
        <v-card-text>
          <v-switch v-model="ae.dias_viaje" label="Durante los últimos 14 días ha estado residiendo o ha
                      viajado desde cualquier país o área de Alto Riesgo"></v-switch>
          <template v-if="ae.dias_viaje">
            <v-text-field v-model="ae.medio_transporte" prepend-icon="mdi-car" label="Medio de transporte"></v-text-field>
            <v-menu
              v-model="menu"
              :close-on-content-click="false"
              :nudge-right="40"
              transition="scale-transition"
              offset-y
              min-width="auto"
            >
              <template v-slot:activator="{ on, attrs }">
                <v-text-field
                  v-model="ae.fecha_llegada"
                  label="Fecha de llegada"
                  prepend-icon="mdi-calendar"
                  readonly
                  v-bind="attrs"
                  v-on="on"
                ></v-text-field>
              </template>
              <v-date-picker
                v-model="ae.fecha_llegada"
                @input="menu = false"
              ></v-date-picker>
            </v-menu>
            <v-text-field v-model="ae.paises_visitados" prepend-icon="mdi-map-marker" label="Enumere todos los países y lugares donde ha residido o
                        viajado en los últimos 14 días (incluso si no está afectado por COVID-19)">
            </v-text-field>
          </template>
          <v-switch v-model="ae.contacto_cercano" label="¿Tuvo contacto cercano (compartiendo alojamiento o
                      proporcionando cuidado)"></v-switch>
          <template v-if="ae.contacto_cercano">
            <v-menu
              v-model="menu2"
              :close-on-content-click="false"
              :nudge-right="40"
              transition="scale-transition"
              offset-y
              min-width="auto"
            >
              <template v-slot:activator="{ on, attrs }">
                <v-text-field
                  v-model="ae.fecha_ultimo_contacto"
                  label="Fecha de último contacto"
                  prepend-icon="mdi-calendar"
                  readonly
                  v-bind="attrs"
                  v-on="on"
                ></v-text-field>
              </template>
              <v-date-picker
                v-model="ae.fecha_ultimo_contacto"
                @input="menu2 = false"
              ></v-date-picker>
            </v-menu>
          </template>
          <v-switch v-model="ae.conv_covid" label="¿Pasó tiempo en la distancia de conversación con una persona
                        que tiene o está bajo investigación por COVID-19?">
          </v-switch>
          <v-text-field v-model="ae.debilite_sistema" label="Condición que debilite el sistema inmune"></v-text-field>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text color="normal" @click="close">ATRAS</v-btn>
          <v-btn color="primary" @click="guardar">GUARDAR</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>

export default {
  props: {
    ae: {
      type: Object,
    },
    showDialog: {
      type: Boolean,
      default: false,
    },
    action: {
      type: String
    }
  },
  data() {
    return {
      menu: false,
      menu2: false,
    }
  },
  methods: {
    async guardar() {
      try {
        const res = await this.$store.dispatch(`admision/${this.action}AE`, this.ae)
        this.$store.commit('SHOW_SUCCESS_SNACKBAR', await res.data.message)
        this.close()
        await this.$store.dispatch('admision/getFichas', this.$store.getters["admision/currentPage"])
      } catch (e) {
        this.$store.commit('SHOW_ERROR_SNACKBAR', await e.response.data.message)
      }
    },
    close() {
      this.$emit('close-dialog')
      this.reset()
    },
    reset() {
      for (const prop in this.ae) {
        if (prop === 'idfichapacientes' || prop === 'paises_visitados' ||
          prop === 'debilite_sistema' || prop === 'medio_transporte' || prop === 'fecha_llegada' ||
          prop === 'fecha_ultimo_contacto') {
          this.ae[prop] = null
        } else {
          this.ae[prop] = false
        }
      }
    }
  },


}
</script>

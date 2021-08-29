<template>
  <div>
    <v-dialog v-model="dialog.nueva_evidencia" persistent max-width="700px">
      <v-card>
        <v-card-title>NUEVA EVIDENCIA</v-card-title>
        <v-divider></v-divider>
        <validation-observer ref="observer" v-slot="{invalid}">
          <v-card-text>
            <v-row>
              <v-col cols="12" lg="4" md="4" sm="12" xs="12">
                <v-select
                  v-model="criterio"
                  :items="criterios"
                  item-text="text"
                  item-value="value"
                  outlined
                  dense
                  label="BUSCAR POR"
                ></v-select>
              </v-col>
              <v-col cols="12" lg="8" md="8" sm="12" xs="12">
                <v-autocomplete
                  :search-input.sync="search_paciente"
                  :items="pacientes"
                  placeholder="Buscar pacientes"
                  hide-no-data
                  return-object
                  dense
                  clearable
                  hide-selected
                  outlined
                  :item-text="criterio"
                  @click:clear="clean"
                >
                  <template v-slot:item="data">
                    <v-list-item-content @click="selectPaciente(data.item)">
                      <v-list-item-title>{{ data.item.nom_completo  }}</v-list-item-title>
                      <v-list-item-subtitle>DNI {{ data.item.numero_documento  }}</v-list-item-subtitle>
                    </v-list-item-content>
                  </template>
                </v-autocomplete>
              </v-col>
            </v-row>
            <validation-provider v-slot="{errors}" name="estacion" rules="required">
              <v-select class="font-weight-medium" dense v-model="form.id_estacion" label="ESTACION" :items="estaciones" :error-messages="errors"
                        prepend-icon="mdi-map-marker" item-value="idestaciones" item-text="nom_estacion" clearable></v-select>
            </validation-provider>
            <validation-provider v-slot="{errors}" name="full name" rules="required">
              <v-text-field label="NOMBRE COMPLETO" :error-messages="errors"
                            v-model="form.nombre_completo" prepend-icon="mdi-account" disabled></v-text-field>
            </validation-provider>
            <validation-provider v-slot="{errors}" name="document number" rules="required">
              <v-text-field label="DNI" :error-messages="errors"
                            v-model="form.numero_documento" prepend-icon="mdi-card-account-details" disabled></v-text-field>
            </validation-provider>
            <validation-provider v-slot="{errors}" name="company" rules="required">
              <v-autocomplete
                :search-input.sync="search_empresa"
                v-model="form.id_empresa"
                :items="empresas"
                label="EMPRESA"
                prepend-icon="mdi-domain"
                hide-no-data
                hide-selected
                dense
                clearable
                item-text="descripcion"
                item-value="idempresa"
                :error-messages="errors"
              >
                <template v-slot:item="data">
                  <v-list-item-content>
                    <v-list-item-title>{{ data.item.descripcion }}</v-list-item-title>
                    <v-list-item-subtitle>{{ data.item.nombrecomercial  }}</v-list-item-subtitle>
                    <v-list-item-subtitle>RUC {{ data.item.ruc  }}</v-list-item-subtitle>
                  </v-list-item-content>
                </template>
              </v-autocomplete>
            </validation-provider>
            <validation-provider v-slot="{errors}" name="phone" rules="required|min:9|integer">
              <v-text-field label="CELULAR" :error-messages="errors" counter type="number"
                            v-model="form.celular" prepend-icon="mdi-phone" clearable></v-text-field>
            </validation-provider>
            <validation-provider v-slot="{errors}" name="email" rules="email">
              <v-text-field label="CORREO" :error-messages="errors"
                            v-model="form.correo" prepend-icon="mdi-email" clearable></v-text-field>
            </validation-provider>
          </v-card-text>
          <v-divider></v-divider>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn text color="normal" @click="close">CANCELAR</v-btn>
            <v-btn :loading="loading" :disabled="invalid" color="primary" @click="store">CREAR</v-btn>
          </v-card-actions>
        </validation-observer>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex';
import { setInteractionMode } from 'vee-validate'

setInteractionMode("aggressive");

export default {
  data() {
    return {
      form: {
        nombre_completo: null,
        id_paciente: null,
        id_empresa: null,
        numero_documento: null,
        celular: null,
        correo: null,
        id_estacion: null,
      },
      pacientes: [],
      empresas: [],
      criterio: "numero_documento",
      criterios: [
        { value: "numero_documento", text: "DNI" },
        { value: "nom_completo", text: "NOMBRES" },
      ],
      search_paciente: null,
      search_empresa: null,
    }
  },
  methods: {
    selectPaciente(paciente) {
      this.form.id_paciente = paciente.idpacientes;
      this.form.numero_documento = paciente.numero_documento;
      this.form.nombre_completo = paciente.nom_completo;
      this.form.celular = paciente.celular;
      this.form.correo = paciente.correo;
      if(paciente.empresa) {
        this.form.id_empresa = paciente.idempresa;
        this.search_empresa = paciente.empresa.descripcion;
      }
    },
    clean() {
      if (this.$refs.observer) this.$refs.observer.reset();
      for (const key of Object.keys(this.form)) {
        this.form[key] = null;
      }
    },
    close () {
      this.$store.commit('rc/SHOW_DIALOG_EVIDENCIA', false);
      this.search_paciente = null;
      this.clean();
    },
    async store () {
      await this.$store.dispatch('rc/store', this.form);
      this.clean();
    }
  },
  computed: {
    ...mapState('rc',['dialog','loading']),
    ...mapGetters(['getSedes']),
    estaciones() {
      return this.$store.state.estaciones.filter(estacion => estacion.idsede > 3 || estacion.idestaciones === 11)
    }
  },
  watch: {
    search_paciente(val) {
      this.pacientes = [];
      axios.get('/api/v1/search?buscar=' + val)
        .then(res => {
          if (res && res.data) {
            this.pacientes = res.data;
          }
        })
        .catch(err => {
          if(err && err.response) {
            console.error(err.response);
          }
        });
    },
    search_empresa(val) {
      if(val) {
        axios.get('/api/v1/searchEmpresa', {
          params: {
            buscar: val
          }
        })
          .then(res => {
            if(res && res.data) {
              this.empresas = res.data;
            }
          })
      }
    },
  }
}
</script>

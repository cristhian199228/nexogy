<template>
  <div>
    <v-col v-if="paciente" cols="12" offset-lg="2" offset-md="1" lg="8" md="10" sm="12" xs="12">
      <v-card-title>EDITAR PACIENTE</v-card-title>
      <validation-observer ref="observer" v-slot="{validate}">
        <v-row dense>
          <v-col cols="12" lg="6" md="6" sm="12" xs="12">
            <validation-provider v-slot="{errors}" name="doctype" rules="required|integer">
              <v-select
                v-model="paciente.tipo_documento"
                :items="documentos"
                item-text="name"
                item-value="id"
                label="TIPO DOCUMENTO"
                dense
                prepend-icon="mdi-card-account-details"
                :error-messages="errors"
              ></v-select>
            </validation-provider>
          </v-col>
          <v-col cols="12" lg="6" md="6" sm="12" xs="12">
            <validation-provider v-slot="{errors}" name="document number" rules="required|min:8">
              <v-text-field
                v-model="paciente.numero_documento"
                dense
                clearable
                prepend-icon="mdi-card-account-details"
                :error-messages="errors"
                label="NUMERO DOCUMENTO"></v-text-field>
            </validation-provider>
          </v-col>
        </v-row>
        <v-row dense>
          <v-col cols="12" lg="6" md="6" sm="12" xs="12">
            <validation-provider v-slot="{errors}" name="name" rules="required|alpha_spaces">
              <v-text-field
                dense
                v-model="paciente.nombres"
                clearable
                prepend-icon="mdi-account"
                label="NOMBRES"
                :error-messages="errors"
              ></v-text-field>
            </validation-provider>
          </v-col>
          <v-col cols="12" lg="6" md="6" sm="12" xs="12">
            <validation-provider v-slot="{errors}" name="apellido paterno" rules="required|alpha_spaces">
              <v-text-field
                v-model="paciente.apellido_paterno"
                prepend-icon="mdi-account"
                dense
                clearable
                label="APELLIDO PATERNO"
                :error-messages="errors"
              ></v-text-field>
            </validation-provider>
          </v-col>
        </v-row>
        <v-row dense>
          <v-col cols="12" lg="6" md="6" sm="12" xs="12">
            <validation-provider v-slot="{errors}" rules="required_if:doctype,1|alpha_spaces" name="apellido materno">
              <v-text-field
                v-model="paciente.apellido_materno"
                clearable
                prepend-icon="mdi-account"
                label="APELLIDO MATERNO"
                dense
                :error-messages="errors"
              ></v-text-field>
            </validation-provider>
          </v-col>
          <v-col cols="12" lg="6" md="6" sm="12" xs="12">
            <v-menu
              v-model="menu"
              :close-on-content-click="false"
              :nudge-right="40"
              transition="scale-transition"
              offset-y
              min-width="auto"
            >
              <template v-slot:activator="{ on, attrs }">
                <validation-provider v-slot="{errors}" rules="required" name="fecha de nacimiento">
                  <v-text-field
                    v-model="paciente.fecha_nacimiento"
                    label="FECHA DE NACIMIENTO"
                    prepend-icon="mdi-calendar"
                    readonly
                    v-bind="attrs"
                    v-on="on"
                    dense
                    :error-messages="errors"
                  ></v-text-field>
                </validation-provider>
              </template>
              <v-date-picker
                v-model="paciente.fecha_nacimiento"
                @input="menu = false"
              ></v-date-picker>
            </v-menu>
          </v-col>
        </v-row>
        <v-row dense>
          <v-col cols="12" lg="6" md="6" sm="12" xs="12">
            <validation-provider v-slot="{errors}" name="sex" rules="required">
              <v-select
                v-model="paciente.sexo"
                :items="sexos"
                item-text="text"
                item-value="value"
                label="SEXO"
                dense
                :prepend-icon="`${ paciente.sexo === 'F' ? 'mdi-gender-female' : 'mdi-gender-male' }`"
                :error-messages="errors"
              ></v-select>
            </validation-provider>
          </v-col>
          <v-col cols="12" lg="6" md="6" sm="12" xs="12">
            <validation-provider v-slot="{errors}" name="departament" rules="required">
              <v-autocomplete
                :search-input.sync="search_departamento"
                :items="departamentos"
                v-model="paciente.residencia_departamento"
                prepend-icon="mdi-map-marker"
                label="DEPARTAMENTO"
                hide-no-data
                hide-selected
                dense
                clearable
                :error-messages="errors"
                item-text="name"
                item-value="id"
              >
              </v-autocomplete>
            </validation-provider>
          </v-col>
        </v-row>
        <v-row dense>
          <v-col cols="12" lg="6" md="6" sm="12" xs="12">
            <validation-provider v-slot="{errors}" name="province" rules="required">
              <v-autocomplete
                :search-input.sync="search_provincia"
                :items="provincias"
                v-model="paciente.residencia_provincia"
                prepend-icon="mdi-map-marker"
                label="PROVINCIA"
                hide-no-data
                hide-selected
                dense
                clearable
                item-text="name"
                item-value="id"
                :error-messages="errors"
              >
              </v-autocomplete>
            </validation-provider>
          </v-col>
          <v-col cols="12" lg="6" md="6" sm="12" xs="12">
            <validation-provider v-slot="{errors}" name="district" rules="required">
              <v-autocomplete
                :search-input.sync="search_distrito"
                :items="distritos"
                v-model="paciente.residencia_distrito"
                prepend-icon="mdi-map-marker"
                label="DISTRITO"
                hide-no-data
                hide-selected
                dense
                clearable
                item-text="name"
                item-value="id"
                :error-messages="errors"
              >
              </v-autocomplete>
            </validation-provider>
          </v-col>
        </v-row>
        <v-row dense>
          <v-col cols="12">
            <v-text-field
              v-model="paciente.direccion"
              prepend-icon="mdi-map-marker"
              label="DIRECCION"
              dense
              clearable
            ></v-text-field>
          </v-col>
        </v-row>
        <v-row dense>
          <v-col cols="12">
            <validation-provider v-slot="{errors}" name="company" rules="required">
              <v-autocomplete
                :search-input.sync="search_empresa"
                v-model="paciente.idempresa"
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
          </v-col>
        </v-row>
        <v-row dense>
          <v-col cols="12" lg="6" md="6" sm="12" xs="12">
            <v-text-field
              v-model="paciente.puesto"
              prepend-icon="mdi-tools"
              dense
              clearable
              label="PUESTO"
            ></v-text-field>
          </v-col>
          <v-col cols="12" lg="6" md="6" sm="12" xs="12">
            <validation-provider v-slot="{errors}" name="celular" rules="min:9">
              <v-text-field
                v-model="paciente.celular"
                prepend-icon="mdi-phone"
                dense
                clearable
                label="CELULAR"
                :error-messages="errors"
              ></v-text-field>
            </validation-provider>
          </v-col>
        </v-row>
        <v-row dense>
          <v-col cols="12" lg="6" md="6" sm="12" xs="12">
            <validation-provider v-slot="{errors}" name="celular" rules="email">
              <v-text-field
                v-model="paciente.correo"
                prepend-icon="mdi-email"
                dense
                clearable
                label="CORREO"
                :error-messages="errors"
              ></v-text-field>
            </validation-provider>
          </v-col>
        </v-row>
        <v-row>
          <v-spacer></v-spacer>
          <v-btn color="normal" text to="/pacientes">CANCELAR</v-btn>
          <v-btn color="primary" @click="guardar">GUARDAR</v-btn>
        </v-row>
      </validation-observer>
    </v-col>
  </div>
</template>

<script>

import { mapActions, mapGetters, mapState} from 'vuex';

export default {
  data() {
    return {
      documentos: [
        {id: 1, name: "DNI"},
        {id: 2, name: "CARNET DE EXTRANJERÍA"},
        {id: 3, name: "PASAPORTE"},
        {id: 7, name: "RUT"},
      ],
      search_empresa: null,
      search_departamento: null,
      search_provincia: null,
      search_distrito: null,
      sexos: [
        { value: "M", text: "MASCULINO" },
        { value: "F", text: "FEMENINO" },
      ],
      menu: false,
    }
  },
  computed: {
    ...mapGetters('pacientes',['getPacienteById']),
    ...mapState(['empresas', 'departamentos', 'provincias', 'distritos']),
    paciente(){
      return this.getPacienteById(this.$route.params.id_pac);
    }
  },
  methods: {
    ...mapActions(['getEmpresas', 'getDepartamentos', 'getProvincias', 'getDistritos']),
    ...mapActions('pacientes',['updatePaciente']),
    guardar(){
      this.$refs.observer.validate().then(isValid => {
        if (isValid) {
          this.updatePaciente(this.paciente)
        }
      })
    },
  },
  watch: {
    search_empresa(val) {
      if(val) this.getEmpresas(val)
    },
    search_departamento(val) {
      if(val) this.getDepartamentos(val)
    },
    search_provincia(val) {
      if(val) this.getProvincias(val)
    },
    search_distrito(val) {
      if (val) this.getDistritos(val)
    }
  },
  created() {
    if (this.paciente && this.paciente.departamento_ubigeo) {
      this.search_departamento = this.paciente.departamento_ubigeo.name;
    }
    if (this.paciente && this.paciente.provincia_ubigeo) {
      this.search_provincia = this.paciente.provincia_ubigeo.name;
    }
    if (this.paciente && this.paciente.distrito_ubigeo) {
      this.search_distrito = this.paciente.distrito_ubigeo.name;
    }
    if (this.paciente && this.paciente.empresa) {
      this.search_empresa = this.paciente.empresa.descripcion;
    }
  }
}
</script>
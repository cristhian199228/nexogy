<template>
  <div>
    <v-row class="mx-2" no-gutters>
      <v-col cols="12" offset-lg="2" offset-md="1" lg="8" md="10" sm="12" xs="12">
        <encabezado-secundario title="NUEVO PACIENTE" />
        <validation-observer ref="observer" v-slot="{validate}">
          <v-row dense>
            <v-col cols="12" lg="6" md="6" sm="12" xs="12">
              <validation-provider v-slot="{errors}" name="doctype" rules="required|integer">
                <v-select
                  v-model="form.tipo_documento"
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
                  v-model="form.numero_documento"
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
                  v-model="form.nombres"
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
                  v-model="form.apellido_paterno"
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
                  v-model="form.apellido_materno"
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
                ref="menu"
                v-model="menu"
                :close-on-content-click="false"
                transition="scale-transition"
                offset-y
                min-width="auto"
              >
                <template v-slot:activator="{ on, attrs }">
                  <validation-provider v-slot="{errors}" name="birth date" rules="required">
                    <v-text-field
                      v-model="form.fecha_nacimiento"
                      label="FECHA NACIMIENTO"
                      prepend-icon="mdi-calendar"
                      readonly
                      dense
                      v-bind="attrs"
                      :error-messages="errors"
                      v-on="on"
                    ></v-text-field>
                  </validation-provider>
                </template>
                <v-date-picker
                  ref="picker"
                  v-model="form.fecha_nacimiento"
                  :max="new Date().toISOString().substr(0, 10)"
                  min="1930-01-01"
                  @change="save"
                ></v-date-picker>
              </v-menu>
            </v-col>
          </v-row>
          <v-row dense>
            <v-col cols="12" lg="6" md="6" sm="12" xs="12">
              <validation-provider v-slot="{errors}" name="sex" rules="required">
                <v-select
                  v-model="form.sexo"
                  :items="sexos"
                  item-text="text"
                  item-value="value"
                  label="SEXO"
                  dense
                  :prepend-icon="`${ form.sexo === 'F' ? 'mdi-gender-female' : 'mdi-gender-male' }`"
                  :error-messages="errors"
                ></v-select>
              </validation-provider>
            </v-col>
            <v-col cols="12" lg="6" md="6" sm="12" xs="12">
              <validation-provider v-slot="{errors}" name="departament" rules="required">
                <v-autocomplete
                  :search-input.sync="search_departamento"
                  :items="departamentos"
                  v-model="form.id_departamento"
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
                  v-model="form.id_provincia"
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
                  v-model="form.id_distrito"
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
              <validation-provider v-slot="{errors}" name="address" rules="required">
                <v-text-field
                  v-model="form.direccion"
                  prepend-icon="mdi-map-marker"
                  label="DIRECCION"
                  dense
                  clearable
                  :error-messages="errors"
                ></v-text-field>
              </validation-provider>
            </v-col>
          </v-row>
          <v-row dense>
            <v-col cols="12">
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
            </v-col>
          </v-row>
          <v-row dense>
            <v-col cols="12" lg="6" md="6" sm="12" xs="12">
              <validation-provider v-slot="{errors}" name="puesto" rules="required">
                <v-text-field
                  v-model="form.puesto"
                  prepend-icon="mdi-tools"
                  dense
                  clearable
                  label="PUESTO"
                  :error-messages="errors"
                ></v-text-field>
              </validation-provider>
            </v-col>
            <v-col cols="12" lg="6" md="6" sm="12" xs="12">
              <validation-provider v-slot="{errors}" name="cellphone" rules="required|min:9|integer">
                <v-text-field
                  v-model="form.celular"
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
              <validation-provider v-slot="{errors}" name="email" rules="email">
                <v-text-field
                  v-model="form.correo"
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
            <v-btn color="normal" text @click="$router.push({name: 'nueva_ficha'})">ATRAS</v-btn>
            <v-spacer></v-spacer>
            <v-btn color="normal" text @click="clean">LIMPIAR</v-btn>
            <v-btn color="primary" @click="register">REGISTRAR PACIENTE</v-btn>
          </v-row>
        </validation-observer>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import { required, email, max, required_if, min, integer, alpha_spaces, alpha } from "vee-validate/dist/rules";
import { extend, ValidationObserver, ValidationProvider, setInteractionMode } from 'vee-validate'
import EncabezadoSecundario from "./EncabezadoSecundario";

setInteractionMode("eager");
extend("required", {
  ...required,
  message: "{_field_} can not be empty"
});
extend("max", {
  ...max,
  message: "{_field_} may not be greater than {length} characters"
});
extend("min", {
  ...min,
  message: "{_field_} may not be less than {length} characters"
});
extend("email", {
  ...email,
  message: "Email must be valid"
});
extend("alpha", {
  ...alpha,
});
extend("alpha_spaces", {
  ...alpha_spaces,
});
extend("integer", {
  ...integer,
});
extend("required_if", {
  ...required_if,
  message: "{_field_} can not be empty"
});

export default {
  components: {
    ValidationProvider,
    ValidationObserver,
    EncabezadoSecundario,
  },
  data() {
    return {
      form: {
        nombres: null,
        apellido_paterno: null,
        apellido_materno: null,
        fecha_nacimiento: null,
        sexo: null,
        id_empresa: null,
        numero_documento: null,
        tipo_documento: null,
        correo: null,
        celular: null,
        direccion: null,
        puesto: null,
        foto: null,
        id_departamento: null,
        id_provincia: null,
        id_distrito: null,
      },
      empresas: [],
      departamentos: [],
      provincias: [],
      distritos: [],
      documentos: [
        {id: 1, name: "DNI"},
        {id: 2, name: "CARNET EXTRANJERÍA"},
        {id: 3, name: "PASAPORTE"},
        {id: 7, name: "RUT"},
      ],
      sexos: [
        { value: "M", text: "MASCULINO" },
        { value: "F", text: "FEMENINO" },
      ],
      menu: false,
      search_empresa: null,
      search_departamento: null,
      search_provincia: null,
      search_distrito: null,
    }
  },
  methods: {
    register() {
      this.$refs.observer.validate().then(valid => {
        if(valid) {
          this.$store.dispatch('admision/storePaciente', this.form);
        }
      })
    },
    clean() {
      for (const key of Object.keys(this.form)) {
        this.form[key] = null;
      }
    },
    save (date) {
      this.$refs.menu.save(date)
    },
  },
  watch: {
    menu (val) {
      val && setTimeout(() => (this.$refs.picker.activePicker = 'YEAR'))
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
    search_departamento(val) {
      if(val) {
        axios.get('/api/v1/searchDepartamento?buscar=' + val)
          .then(res => {
            if(res && res.data) {
              this.departamentos = res.data.departamentos;
            }
          })
          .catch(err => {
            if(err && err.response) {
              console.error(err.response)
            }
          })
      }
    },
    search_provincia(val) {
      if(val) {
        axios.get('/api/v1/searchProvincia?buscar=' + val)
          .then(res => {
            if(res && res.data) {
              this.provincias = res.data.provincias;
            }
          })
          .catch(err => {
            if(err && err.response) {
              console.error(err.response)
            }
          })
      }
    },
    search_distrito(val) {
      if(val) {
        axios.get('/api/v1/searchDistrito?buscar=' + val)
          .then(res => {
            if(res && res.data) {
              this.distritos = res.data.distritos;
            }
          })
          .catch(err => {
            if(err && err.response) {
              console.error(err.response)
            }
          })
      }
    }
  },
}
</script>

<template>
  <div>
    <v-overlay :value="overlay">
      <v-progress-circular indeterminate size="70"></v-progress-circular>
    </v-overlay>
    <v-row class="mx-2" no-gutters>
      <v-col
        cols="12"
        offset-lg="2"
        offset-md="1"
        lg="8"
        md="10"
        sm="12"
        xs="12"
      >
        <encabezado-secundario title="NUEVA ATENCIÓN" />
        <validation-observer ref="observer" v-slot="{ validate }">
          <v-row dense>
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
                :messages="message"
                @click:clear="clean"
              >
                <template v-slot:item="data">
                  <v-list-item-content @click="selectPaciente(data.item)">
                    <v-list-item-title>{{
                      data.item.nom_completo
                    }}</v-list-item-title>
                    <v-list-item-subtitle
                      >DNI
                      {{ data.item.numero_documento }}</v-list-item-subtitle
                    >
                  </v-list-item-content>
                </template>
              </v-autocomplete>
            </v-col>
          </v-row>
          <v-row dense>
            <v-col cols="12" lg="5" md="5" sm="4" xs="12">
              <v-img
                v-if="form.id_paciente && form.foto"
                contain
                :src="'/api/v1/fp/' + form.foto"
                height="250"
                alt="NO TIENE FOTO"
              ></v-img>
            </v-col>
            <v-col cols="12" lg="7" md="7" sm="8" xs="12">
              <validation-provider
                v-slot="{ errors }"
                name="doctype"
                rules="required"
              >
                <v-select
                  v-model="form.tipo_documento"
                  :items="tipo_documento_opt"
                  item-text="name"
                  item-value="id"
                  label="TIPO DOCUMENTO"
                  prepend-icon="mdi-card-account-details"
                  :error-messages="errors"
                  disabled
                ></v-select>
              </validation-provider>
              <validation-provider
                v-slot="{ errors }"
                name="document number"
                rules="required"
              >
                <v-text-field
                  v-model="form.numero_documento"
                  prepend-icon="mdi-card-account-details"
                  disabled
                  :error-messages="errors"
                  label="NUMERO DOCUMENTO"
                ></v-text-field>
              </validation-provider>
              <validation-provider
                v-slot="{ errors }"
                name="turno"
                rules="required"
              >
                <v-select
                  v-model="form.turno"
                  :items="turnos"
                  item-text="name"
                  item-value="id"
                  label="TURNO"
                  :error-messages="errors"
                  prepend-icon="mdi-account-hard-hat"
                ></v-select>
              </validation-provider>
              <validation-provider
                v-slot="{ errors }"
                name="rol"
                rules="required"
              >
                <v-select
                  v-model="form.rol"
                  :items="roles"
                  item-text="name"
                  item-value="id"
                  label="ROL"
                  :error-messages="errors"
                  prepend-icon="mdi-account-hard-hat"
                ></v-select>
              </validation-provider>
            </v-col>
          </v-row>
          <v-row dense>
            <v-col cols="12">
              <validation-provider
                v-slot="{ errors }"
                name="full name"
                rules="required"
              >
                <v-text-field
                  v-model="form.nombre_completo"
                  prepend-icon="mdi-account"
                  disabled
                  label="NOMBRE COMPLETO"
                  dense
                  :error-messages="errors"
                ></v-text-field>
              </validation-provider>
            </v-col>
          </v-row>
          <v-row dense>
            <v-col cols="12">
              <validation-provider
                v-slot="{ errors }"
                name="company"
                rules="required"
              >
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
                      <v-list-item-title>{{
                        data.item.descripcion
                      }}</v-list-item-title>
                      <v-list-item-subtitle>{{
                        data.item.nombrecomercial
                      }}</v-list-item-subtitle>
                      <v-list-item-subtitle
                        >RUC {{ data.item.ruc }}</v-list-item-subtitle
                      >
                    </v-list-item-content>
                  </template>
                </v-autocomplete>
              </validation-provider>
            </v-col>
          </v-row>
          <v-row dense>
            <v-col cols="12" lg="6" md="6" sm="12" xs="12">
              <validation-provider
                v-slot="{ errors }"
                name="puesto"
                rules="required|min:5"
              >
                <v-text-field
                  v-model="form.puesto"
                  prepend-icon="mdi-tools"
                  label="PUESTO"
                  dense
                  clearable
                  :error-messages="errors"
                ></v-text-field>
              </validation-provider>
            </v-col>
            <v-col cols="12" lg="6" md="6" sm="12" xs="12">
              <validation-provider
                v-slot="{ errors }"
                name="phone"
                rules="required|min:9|integer"
              >
                <v-text-field
                  v-model="form.celular"
                  prepend-icon="mdi-phone"
                  label="CELULAR"
                  dense
                  clearable
                  :error-messages="errors"
                >
                  <template v-slot:append-outer>
                    <v-btn
                      icon
                      :disabled="
                        form.id_paciente == null || form.celular == null
                      "
                      @click="enviarMensajePrueba"
                    >
                      <v-icon>mdi-whatsapp</v-icon>
                    </v-btn>
                  </template>
                </v-text-field>
              </validation-provider>
            </v-col>
          </v-row>
          <v-row dense>
            <v-col cols="12" lg="6" md="6" sm="12" xs="12">
              <validation-provider
                v-slot="{ errors }"
                name="email"
                rules="email"
              >
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
            <v-col cols="12" lg="6" md="6" sm="12" xs="12">
              <validation-provider
                v-slot="{ errors }"
                name="departament"
                rules="required"
              >
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
              <validation-provider
                v-slot="{ errors }"
                name="province"
                rules="required"
              >
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
              <validation-provider
                v-slot="{ errors }"
                name="district"
                rules="required"
              >
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
              <validation-provider
                v-slot="{ errors }"
                name="address"
                rules="required"
              >
                <v-text-field
                  v-model="form.direccion"
                  prepend-icon="mdi-map-marker"
                  label="DIRECCIÓN"
                  dense
                  clearable
                  :error-messages="errors"
                ></v-text-field>
              </validation-provider>
            </v-col>
            <v-col cols="12">
              <v-checkbox
                color="success"
                v-model="form.enviar_mensaje"
                @change="enviarMensajeHandler"
                hide-details
                label="Enviar resultados por whatsapp"
              ></v-checkbox>
            </v-col>
            <v-col v-if="form.enviar_mensaje" cols="12">
              <div class="custom-red">
                <v-switch
                  v-model="form.numero_verificado"
                  label="Numero verificado"
                  color="success"
                ></v-switch>
              </div>

              <!--
              <v-checkbox color="success" v-model="form.numero_verificado" hide-details>
                <template v-slot:label>
                  <div :class="form.numero_verificado ? 'green--text' : 'red--text'">Numero verificado</div>
                </template>
              </v-checkbox>-->
            </v-col>
          </v-row>
          <v-divider></v-divider>
          <v-row dense>
            <v-btn
              color="normal"
              text
              @click="$router.push({ name: 'tabla_admision' })"
              >ATRÁS</v-btn
            >
            <v-spacer></v-spacer>
            <v-btn color="normal" text @click="clean">LIMPIAR</v-btn>
            <v-btn color="primary" :loading="loading" @click="crearFicha"
              >REGISTRAR ATENCIÓN</v-btn
            >
          </v-row>
        </validation-observer>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import EncabezadoSecundario from "./EncabezadoSecundario";
import { mapState } from "vuex";

export default {
  components: {
    EncabezadoSecundario,
  },
  data() {
    return {
      form: {
        id_paciente: null,
        id_empresa: null,
        nombre_completo: null,
        numero_documento: null,
        tipo_documento: null,
        rol: null,
        turno: null,
        correo: null,
        celular: null,
        direccion: null,
        puesto: null,
        foto: null,
        id_departamento: null,
        id_provincia: null,
        id_distrito: null,
        enviar_mensaje: true,
        numero_verificado: false,
      },
      pacientes: [],
      empresas: [],
      departamentos: [],
      provincias: [],
      distritos: [],
      roles: [
        { id: 1, name: "ACUARTELADO" },
        { id: 2, name: "ITINERANTE" },
        { id: 3, name: "NO APLICA" },
      ],
      turnos: [
        { id: 1, name: "SUBIDA - PRE EMBARQUE" },
        { id: 2, name: "BAJADA - POST ROTACIÓN" },
        { id: 3, name: "NO APLICA" },
      ],
      tipo_documento_opt: [
        { id: 1, name: "DNI" },
        { id: 2, name: "PASAPORTE" },
        { id: 3, name: "CARNET EXTRANJERIA" },
        { id: 7, name: "RUT" },
      ],
      criterio: "numero_documento",
      criterios: [
        { value: "numero_documento", text: "NUMERO DOCUMENTO" },
        { value: "nom_completo", text: "NOMBRES" },
      ],
      search_paciente: null,
      search_empresa: null,
      search_departamento: null,
      search_provincia: null,
      search_distrito: null,
      overlay: false,
      message: "",
    };
  },
  methods: {
    selectPaciente(paciente) {
      this.isLoading = false;
      this.form.id_paciente = paciente.idpacientes;
      this.form.tipo_documento = Number(paciente.tipo_documento);
      if (paciente.foto) this.form.foto = paciente.foto;
      this.form.numero_documento = paciente.numero_documento;
      if (paciente.empresa) {
        this.form.id_empresa = paciente.idempresa;
        this.search_empresa = paciente.empresa.descripcion;
      }
      if (paciente.programacion_cv.length > 0) {
        this.form.turno = paciente.programacion_cv[0].turno;
        this.form.rol = paciente.programacion_cv[0].rol;
      }
      if (paciente.departamento_ubigeo) {
        this.form.id_departamento = paciente.departamento_ubigeo.id;
        this.search_departamento = paciente.departamento_ubigeo.name;
      }
      if (paciente.provincia_ubigeo) {
        this.form.id_provincia = paciente.provincia_ubigeo.id;
        this.search_provincia = paciente.provincia_ubigeo.name;
      }
      if (paciente.distrito_ubigeo) {
        this.form.id_distrito = paciente.distrito_ubigeo.id;
        this.search_distrito = paciente.distrito_ubigeo.name;
      }
      this.form.puesto = paciente.puesto;
      this.form.celular = paciente.celular;
      this.form.correo = paciente.correo;
      this.form.direccion = paciente.direccion;
      this.form.nombre_completo = paciente.nom_completo;
    },
    enviarMensajePrueba() {
      const res = confirm("Esta seguro de enviar el mensaje de prueba?");
      if (res) this.$store.dispatch("controlador/enviarWpPrueba", this.form);
    },
    enviarMensajeHandler(val) {
      if (!val) {
        this.form.numero_verificado = false;
      }
    },
    crearFicha() {
      this.$refs.observer.validate().then((valid) => {
        if (!valid) return
        axios.get("api/v1/ficha/" + this.form.id_paciente)
          .then((res) => {
            if (res && res.data) {
              if (res.data.tiene_atencion) {
                let r = confirm(
                  "Ya existe una atención para este paciente, desea crear una atención adicional?"
                );
                if (r) this.$store.dispatch("admision/storeFicha", this.form);
              } else {
                this.$store.dispatch("admision/storeFicha", this.form);
              }
            }
          })
          .catch((err) => {
            if (err && err.response) {
              const snackbar = {
                show: true,
                color: "error",
                message: err.response.data.message,
              };
              this.$store.commit("SHOW_SNACKBAR", snackbar);
            }
          });
      });
    },
    clean() {
      this.$refs.observer.reset();
      for (const key of Object.keys(this.form)) {
        this.form[key] = null;
      }
    },
    buscarPaciente(buscar) {
      this.pacientes = [];
      axios
        .get("/api/v1/search?buscar=" + buscar)
        .then((res) => {
          if (res && res.data) {
            this.message = "Busqueda finalizada";
            this.pacientes = res.data;
            if (this.pacientes.length === 0) {
              let r = confirm(
                "No se encontró al paciente en la base de datos, desea registrarlo?"
              );
              if (r) {
                this.overlay = true;
                let tipo_doc = 1;
                if (this.search_paciente.length > 8) tipo_doc = 3;
                else if (isNaN(Number(this.search_paciente))) tipo_doc = 2;

                axios
                  .post("api/v1/storeMinsa", {
                    numero_documento: this.search_paciente,
                    tipo_documento: tipo_doc,
                  })
                  .then((res) => {
                    if (res && res.data) {
                      this.overlay = false;
                      const snackbar = {
                        show: true,
                        color: "success",
                        message: "Paciente registrado correctamente!",
                      };
                      this.$store.commit("SHOW_SNACKBAR", snackbar);
                      this.message = "Buscando....";
                      this.delayedMethod(res.data.paciente.numero_documento);
                    }
                  })
                  .catch((err) => {
                    if (err && err.response) {
                      this.overlay = false;
                      let r = confirm(
                        "No se encontraron datos, por favor regístrese manualmente"
                      );
                      if (r) this.$router.push({ name: "nuevo_paciente" });
                    }
                  });
              }
            }
          }
        })
        .catch((err) => {
          if (err && err.response) {
            console.error(err.response);
          }
        });
    },
  },
  computed: {
    ...mapState("admision", ["loading"]),
  },
  watch: {
    search_paciente(val) {
      if (val && val.length > 7) {
        this.message = "Buscando....";
        this.delayedMethod(val);
      } else {
        if (this.criterio === "numero_documento") {
          this.message = "Ingrese el documento completo del paciente";
        }
      }
    },
    search_empresa(val) {
      if (val) {
        axios
          .get("/api/v1/searchEmpresa", {
            params: {
              buscar: val,
            },
          })
          .then((res) => {
            if (res && res.data) {
              this.empresas = res.data;
            }
          });
      }
    },
    search_departamento(val) {
      if (val) {
        axios
          .get("/api/v1/searchDepartamento?buscar=" + val)
          .then((res) => {
            if (res && res.data) {
              this.departamentos = res.data.departamentos;
            }
          })
          .catch((err) => {
            if (err && err.response) {
              console.error(err.response);
            }
          });
      }
    },
    search_provincia(val) {
      if (val) {
        axios
          .get("/api/v1/searchProvincia?buscar=" + val)
          .then((res) => {
            if (res && res.data) {
              this.provincias = res.data.provincias;
            }
          })
          .catch((err) => {
            if (err && err.response) {
              console.error(err.response);
            }
          });
      }
    },
    search_distrito(val) {
      if (val) {
        axios
          .get("/api/v1/searchDistrito?buscar=" + val)
          .then((res) => {
            if (res && res.data) {
              this.distritos = res.data.distritos;
            }
          })
          .catch((err) => {
            if (err && err.response) {
              console.error(err.response);
            }
          });
      }
    },
  },
  created() {
    this.delayedMethod = _.debounce(this.buscarPaciente, 2000);
    this.form.enviar_mensaje = true;
  },
};
</script>
<style>
.custom-red .v-input--selection-controls__input div {
  color: red;
}
</style>

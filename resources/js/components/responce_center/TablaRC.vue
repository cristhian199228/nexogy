<template>
  <div>
    <viewer :images="images" @inited="inited" class="viewer" ref="viewer">
      <img v-for="src in images" :src="src" class="image" />
    </viewer>
    <buscador-pcr-salud :store="modulo" title="EVIDENCIAS" />
    <v-data-table :headers="headers" :items="evidencias" :loading="loading_table" :items-per-page="35" hide-default-footer>
<!--      <template v-slot:header.fecha="{header}">
        <filtro-fecha :module="modulo" />
      </template>-->
      <template v-slot:header.estacion="{header}">
        <filtro-estacion :module="modulo" />
      </template>
<!--      <template v-slot:header.sede="{header}">
        <filtro-sede :module="modulo" />
      </template>-->
<!--      <template v-slot:header.nom_completo="{header}">
        <filtro-buscar :module="modulo" />
      </template>-->
<!--      <template v-slot:header.emp="{header}">
        <filtro-empresa :module="modulo" />
      </template>-->
      <template v-slot:item.contador="{item}">
        <small>{{ item.contador }}</small>
      </template>
      <template v-slot:item.fecha="{item}">
        <small>{{ item.fecha }}</small>
      </template>
      <template v-slot:item.estacion="{item}">
        <small>{{ item.estacion.nom }}</small>
      </template>
      <template v-slot:item.sede="{item}">
        <small>{{ item.estacion.sede.descripcion }}</small>
      </template>
      <template v-slot:item.user="{item}">
        <small>{{ item.user }}</small>
      </template>
      <template v-slot:item.nom_completo="{item}">
        <small>{{ item.paciente.nom_completo }}</small>
        <v-tooltip v-if="item.ficha_ep && item.ficha_ep.contactos.length > 0" bottom>
          <template v-slot:activator="{ on, attrs }">
            <v-icon dark color="red" v-bind="attrs" v-on="on">mdi-human-male-male</v-icon>
          </template>
          <span>{{ item.ficha_ep.contactos.length }} {{ item.ficha_ep.contactos.length > 1 ? 'contactos directos' : 'contacto directo' }}</span>
        </v-tooltip>
        <template v-if="item.ficha_ep">
          <v-tooltip v-if="item.ficha_ep.firma" bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-icon dark color="green" v-bind="attrs" v-on="on">mdi-text-box-outline</v-icon>
            </template>
            <span>Ficha epidemiológica firmada</span>
          </v-tooltip>
          <v-tooltip v-else bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-icon dark color="red" v-bind="attrs" v-on="on">mdi-text-box-remove-outline</v-icon>
            </template>
            <span>Ficha epidemiológica no firmada</span>
          </v-tooltip>
        </template>
        <template v-if="item.ficha_cam">
          <v-tooltip v-if="item.ficha_cam.firma" bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-icon dark color="green" v-bind="attrs" v-on="on">mdi-text-box-outline</v-icon>
            </template>
            <span>Ficha CAM firmada</span>
          </v-tooltip>
          <v-tooltip v-else bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-icon dark color="red" v-bind="attrs" v-on="on">mdi-text-box-remove-outline</v-icon>
            </template>
            <span>Ficha CAM no firmada</span>
          </v-tooltip>
        </template>
      </template>
      <template v-slot:item.emp="{item}">
        <small v-if="item.paciente.empresa">{{ item.paciente.empresa.descripcion }}</small>
      </template>
      <template v-slot:item.dni="{item}">
        <small>{{ item.paciente.numero_documento }}</small>
      </template>
      <template v-slot:item.reg="{item}">
        <small>{{ item.paciente.nro_registro }}</small>
      </template>
      <template v-slot:item.foto="{item}">
        <template v-if="item.puede_subir_fotos">
          <v-tooltip bottom v-if="!item.estado">
            <template v-slot:activator="{ on, attrs }">
              <v-btn @click="uploadFev(item.id)" icon v-bind="attrs" v-on="on" small><v-icon small>mdi-cloud-upload-outline</v-icon></v-btn>
            </template>
            <span>Subir Foto</span>
          </v-tooltip>
          <v-tooltip v-if="item.imgs.length > 0" bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn @click="verFotos(item.imgs)" icon v-bind="attrs" v-on="on" small><v-icon small>mdi-eye</v-icon></v-btn>
            </template>
            <span>Ver fotos</span>
          </v-tooltip>
          <v-tooltip v-if="item.pdfs.length > 0" bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn @click="verPdfs(item.pdfs)" icon v-bind="attrs" v-on="on" small><v-icon small>mdi-file-pdf</v-icon></v-btn>
            </template>
            <span>Ver PDFs</span>
          </v-tooltip>
        </template>
        <template v-else>
          <v-tooltip bottom v-if="!item.estado">
            <template v-slot:activator="{ on, attrs }">
              <v-btn @click="habilitarFotos(item)" icon v-bind="attrs" v-on="on"><v-icon>mdi-camera-plus-outline</v-icon></v-btn>
            </template>
            <span>Habilitar Fotos</span>
          </v-tooltip>
        </template>
      </template>
      <template v-slot:item.fe="{item}">
        <template v-if="item.ficha_ep">
          <template v-if="!item.estado">
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn @click="editarFe(item)" small icon v-bind="attrs" v-on="on"><v-icon small>mdi-pencil</v-icon></v-btn>
              </template>
              <span>Editar Ficha Ep.</span>
            </v-tooltip>
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn @click="deleteFe(item)" small icon v-bind="attrs" v-on="on"><v-icon small>mdi-delete</v-icon></v-btn>
              </template>
              <span>Eliminar Ficha Ep.</span>
            </v-tooltip>
          </template>
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn @click="pdfFichaEp(item.id)" small icon v-bind="attrs" v-on="on"><v-icon small>mdi-file-document</v-icon></v-btn>
            </template>
            <span>Generar PDF</span>
          </v-tooltip>
        </template>
        <template v-else>
          <v-tooltip bottom v-if="!item.estado">
            <template v-slot:activator="{ on, attrs }">
              <v-btn @click="nuevaFe(item)" icon v-bind="attrs" v-on="on"><v-icon>mdi-text-box-plus-outline</v-icon></v-btn>
            </template>
            <span>Habilitar Ficha Ep.</span>
          </v-tooltip>
        </template>
      </template>
      <template v-slot:item.cam="{item}">
        <template v-if="item.ficha_cam">
          <template v-if="!item.estado">
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn @click="editarFc(item)" small icon v-bind="attrs" v-on="on"><v-icon small>mdi-pencil</v-icon></v-btn>
              </template>
              <span>Editar Ficha CAM</span>
            </v-tooltip>
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn @click="deleteFc(item)" small icon v-bind="attrs" v-on="on"><v-icon small>mdi-delete</v-icon></v-btn>
              </template>
              <span>Eliminar Ficha CAM</span>
            </v-tooltip>
          </template>
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn @click="pdfFichaCam(item.id)" small icon v-bind="attrs" v-on="on"><v-icon small>mdi-file-document</v-icon></v-btn>
            </template>
            <span>Generar PDF</span>
          </v-tooltip>
        </template>
        <template v-else>
          <v-tooltip bottom v-if="!item.estado">
            <template v-slot:activator="{ on, attrs }">
              <v-btn @click="nuevaFc(item)" icon v-bind="attrs" v-on="on"><v-icon>mdi-text-box-plus-outline</v-icon></v-btn>
            </template>
            <span>Habilitar Ficha CAM</span>
          </v-tooltip>
        </template>
      </template>

      <template v-slot:item.im="{item}">
        <template v-if="item.indicaciones_count > 0">
          <template v-if="!item.estado">
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn @click="editarIm(item)" small icon v-bind="attrs" v-on="on"><v-icon small>mdi-pencil</v-icon></v-btn>
              </template>
              <span>Editar Indicaciones Médicas</span>
            </v-tooltip>
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn @click="deleteIm(item.id)" small icon v-bind="attrs" v-on="on"><v-icon small>mdi-delete</v-icon></v-btn>
              </template>
              <span>Eliminar Indicaciones Médicas</span>
            </v-tooltip>
          </template>
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn @click="pdfIndicacionesMedicas(item.id)" small icon v-bind="attrs" v-on="on"><v-icon small>mdi-file-document</v-icon></v-btn>
            </template>
            <span>Generar PDF</span>
          </v-tooltip>
        </template>
        <template v-else>
          <v-tooltip bottom v-if="!item.estado">
            <template v-slot:activator="{ on, attrs }">
              <v-btn @click="storeIm(item)" icon v-bind="attrs" v-on="on"><v-icon>mdi-text-box-plus-outline</v-icon></v-btn>
            </template>
            <span>Habilitar Indicaciones Médicas</span>
          </v-tooltip>
        </template>
      </template>

<!--      <template v-slot:item.estado="{item}">
        <template v-if="item.estado">
          <v-icon color="green darken-2">mdi-check-bold</v-icon>
        </template>
        <template v-else>
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-icon @click="completar(item)" v-bind="attrs" v-on="on" color="orange darken-2">mdi-check-bold</v-icon>
            </template>
            <span>Completar evidencia</span>
          </v-tooltip>
        </template>
      </template>-->
      <template v-slot:item.actions="{item}">
        <template v-if="item.estado">
          <v-btn icon small><v-icon small color="green darken-2">mdi-check-bold</v-icon></v-btn>
        </template>
        <template v-else>
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn small icon>
                <v-icon small @click="completar(item)" v-bind="attrs" v-on="on" color="orange darken-2">mdi-check-bold</v-icon>
              </v-btn>
            </template>
            <span>Completar evidencia</span>
          </v-tooltip>
        </template>

        <v-tooltip v-if="!item.estado" bottom>
          <template v-slot:activator="{ on, attrs }">
            <v-btn @click="eliminar(item)" small icon v-bind="attrs" v-on="on"><v-icon small>mdi-delete</v-icon></v-btn>
          </template>
          <span>Eliminar evidencia</span>
        </v-tooltip>
        <v-tooltip v-if="item.ficha_ep || item.indicaciones_count > 0 || item.ficha_cam || item.imgs.length > 0 || item.pdfs.length > 0" bottom>
          <template v-slot:activator="{ on, attrs }">
            <v-btn @click="downloadFiles(item)" small icon v-bind="attrs" v-on="on"><v-icon small>mdi-download</v-icon></v-btn>
          </template>
          <span>Descargar documentos</span>
        </v-tooltip>
      </template>
    </v-data-table>
    <paginate :store="modulo" />
    <v-btn class="mr-15" @click="nuevaEvidencia" color="pink" fixed bottom right fab dark><v-icon>mdi-plus</v-icon></v-btn>
    <update-fab-button :store="modulo" />
    <NuevaEvidencia />
    <DialogUploadPhoto :store="modulo" :resource="recurso" :title="title"  />
    <DialogReporte :store="modulo" />
    <DialogVerPdf :files="archivos" />
  </div>
</template>

<script>
import { mapState, mapGetters } from "vuex";
import paginate from "../paginate";
import buscadorPcrSalud from "../buscadorPcrSalud";
import Viewer from "v-viewer/src/component.vue"
import NuevaEvidencia from "./NuevaEvidencia";
import DialogUploadPhoto from "../DialogUploadPhoto";
import UpdateFabButton from "../UpdateFabButton";
import DialogReporte from "../DialogReporte";
import FiltroEstacion from "../headers/FiltroEstacion";
import FiltroSede from "../headers/FiltroSede";
import FiltroEmpresa from "../headers/FiltroEmpresa";
import DialogVerPdf from "./DialogVerPdf";
import FiltroBuscar from "../headers/FiltroBuscar";
import FiltroFecha from "../headers/FiltroFecha";

export default {
  components: {
    paginate,
    buscadorPcrSalud,
    Viewer,
    NuevaEvidencia,
    DialogUploadPhoto,
    UpdateFabButton,
    DialogReporte,
    FiltroEstacion,
    DialogVerPdf,
    FiltroSede,
    FiltroEmpresa,
    FiltroBuscar,
    FiltroFecha,
  },
  data() {
    return {
      modulo: "rc",
      recurso: "",
      title: "",
      headers: [
        { text: 'N°', align: 'start', value: 'contador', sortable: false  },
        { text: 'Fecha', value: 'fecha', sortable: false  },
        //{ text: 'Sede', value: 'sede', sortable: false  },
        { text: 'Estacion', value: 'estacion', sortable: false  },
        { text: 'Usuario', value: 'user', sortable: false  },
        { text: 'Paciente', value: 'nom_completo', sortable: false },
        { text: 'Empresa', value: 'emp', sortable: false },
        { text: 'DNI', value: 'dni', sortable: false },
        { text: 'N° Registro', value: 'reg', sortable: false },
        { text: 'Fotos', value: 'foto', sortable: false },
        { text: 'Ficha Ep.', value: 'fe', sortable: false },
        { text: 'Ficha CAM', value: 'cam', sortable: false },
        //{ text: 'IM', value: 'im', sortable: false },
        //{ text: 'Estado', value: 'estado', sortable: false },
        { text: 'Acciones', value: 'actions', sortable: false }
      ],
      images: [],
      archivos: {},
    }
  },
  methods: {
    eliminar(ev) {
      const r = confirm(`Está seguro de eliminar la evidencia del paciente ${ev.paciente.nom_completo}?, se borrarán las fotos y los formatos creados.`);
      if (r) this.$store.dispatch('rc/delete', ev.id);
    },
    nuevaEvidencia () {
      this.$store.commit('rc/SHOW_DIALOG_EVIDENCIA', true);
    },
    nuevaFe(ev) {
      const r = confirm(`Está seguro de habilitar la ficha epidemiológica del paciente ${ev.paciente.nom_completo}?`);
      if (r) this.$store.dispatch('rc/storeFe', ev.id);
    },
    storeIm(ev) {
      const r = confirm(`Recuerde que necesita haber creado el descanso médico en mediweb previamente. Esta seguro de proceder?`)
      if (r) this.$store.dispatch('rc/storeIndicacionesMedicas', ev.id)
    },
    editarFe(ev) {
      this.$store.commit('rc/SET_ID_EV', ev.id);
      this.$store.commit('rc/SET_ID_FE', ev.ficha_ep.id);
      this.$router.push({name: 'EditarFe'});
    },
    editarIm(ev) {
      this.$router.push({
        name: 'EditarIm',
        params: { idEvidencia: ev.id }
      })
    },
    editarFc(ev) {
      this.$store.commit('rc/SET_ID_EV', ev.id);
      this.$store.commit('rc/SET_ID_FC', ev.ficha_cam.id);
      this.$router.push({name: 'EditarFc'});
    },
    deleteFe(ev) {
      const r = confirm(`Está seguro de eliminar la ficha epidemiológica del paciente ${ev.paciente.nom_completo}?`);
      if (r) this.$store.dispatch('rc/deleteFe', ev.ficha_ep.id);
    },
    deleteIm(id) {
      const r = confirm('Está seguro de eliminar las indicaciones médicas?');
      if (r) this.$store.dispatch('rc/deleteIndicacionesMedicas', id)
    },
    nuevaFc(ev) {
      const r = confirm(`Está seguro de habilitar la ficha CAM del paciente ${ev.paciente.nom_completo}?`);
      if (r) this.$store.dispatch('rc/storeFc', ev.id);
    },
    deleteFc(ev) {
      const r = confirm(`Está seguro de eliminar la ficha CAM del paciente ${ev.paciente.nom_completo}?`);
      if (r) this.$store.dispatch('rc/deleteFc', ev.ficha_cam.id);
    },
    habilitarFotos(ev) {
      const r = confirm(`Está seguro de habilitar la subida de fotos del paciente ${ev.paciente.nom_completo}?`);
      if (r) this.$store.dispatch('rc/habilitarFotos', ev.id);
    },
    completar(ev) {
      const r = confirm(`Está seguro de completar la evidencia del paciente ${ev.paciente.nom_completo}?`);
      if (r) this.$store.dispatch('rc/update', ev.id);
    },
    uploadFev(id_ev) {
      this.$store.commit('SET_PHOTO', undefined);
      this.resetChildrenRefs();
      this.$store.commit('rc/SET_ID_EV', id_ev);
      this.recurso = "FEV";
      this.title = "EVIDENCIA";
      this.$store.commit('SHOW_DIALOG_UPLOAD_FOTO', true);
    },
    verFotos(fotos){
      this.images = [];
      fotos.forEach(foto => {
        this.images.push(`api/v1/fev/${foto.path}`)
      });
      this.$viewer.show();
    },
    verPdfs(pdfs) {
      this.archivos = Object.assign({}, pdfs)
      this.$store.commit('rc/SHOW_DIALOG_PDF', true)
    },
    inited (viewer) {
      this.$viewer = viewer
    },
    resetChildrenRefs() {
      if (this.$children && this.$children.length > 0) {
        for (let i = 0; i < this.$children.length; i++ ) {
          if (this.$children[i].$refs && this.$children[i].$refs.photo) {
            this.$children[i].$refs.photo.value = null;
          }
        }
      }
    },
    pdfFichaEp(id_evidencia) {
      window.open( `api/v1/fichaEp/${id_evidencia}`, "_blank");
    },
    pdfFichaCam(id_evidencia) {
      window.open( `api/v1/fichaCam/${id_evidencia}`, "_blank");
    },
    pdfIndicacionesMedicas(id) {
      window.open( `api/v1/fichaIm/${id}`, "_blank");
    },
    downloadFiles ({id}) {
      window.open( `api/v1/fev/download/${id}`, "_blank");
    },
  },
  computed: {
    ...mapState('rc',['loading_table']),
    ...mapGetters(['getEstacion']),
    evidencias: {
      get() {
        return this.$store.state.rc.data.data;
      }
    },
  },
  created() {
    this.$store.dispatch('getEstaciones')
    this.$store.dispatch('getSedes')
    this.$store.dispatch('rc/getFichas', 1);
  }
}
</script>

<style>
.image{
  display: none;
}
</style>


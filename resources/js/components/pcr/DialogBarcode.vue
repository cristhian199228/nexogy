<template>
  <div>
    <v-dialog persistent v-model="dialog.barcode" max-width="600px">
      <v-card>
        <v-card-title>Impresión de código de barras</v-card-title>
        <v-card-text>
          <v-row justify="center">
            <div id="pdf">
              <vue-barcode :value="transaction_id" :height="50" :width="3" :fontSize="13" :margin="0" :marginBottom="2">
                No se pudo generar el codigo de barra
              </vue-barcode>
              <span style="font-size: 0.7em;font-family: Arial;font-weight: bold; margin: 0;">
                               {{ dni + " " + nombre_completo }}
                            </span>
            </div>
          </v-row>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="normal" text @click="close">CANCELAR</v-btn>
          <v-btn color="primary" @click="printBarcode"><v-icon left dark>mdi-printer</v-icon>IMPRIMIR</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>

import { mapState } from 'vuex';
import VueBarcode from 'vue-barcode';
export default {
  components: {
    VueBarcode
  },
  props: ['transaction_id','nombre_completo','dni'],
  computed: {
    ...mapState('pcr',['dialog'])
  },
  methods: {
    close() {
      this.$store.commit('pcr/SHOW_DIALOG_BARCODE', false);
    },
    printBarcode() {
      var pegar_datos = document.getElementById('pdf').innerHTML;
      var janela = window.open('','','width=800','height=600');
      janela.document.write('<html><head>');
      janela.document.write('<title>BARCODE</title>');
      janela.document.write('' +
        '<style type="text/css"> @page {margin-top: 0cm;margin-right: 0cm; margin-bottom: 0cm; margin-left: 5mm; text-align: center;}</style></head>');
      janela.document.write('<body>');
      janela.document.write(pegar_datos);
      janela.document.write('</body></html>');
      janela.document.close();
      janela.print();
    }
  },
}
</script>

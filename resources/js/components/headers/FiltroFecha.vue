<template>
  <div>
    <v-menu
      ref="menu"
      v-model="menu"
      :close-on-content-click="false"
      :return-value.sync="dates"
      transition="scale-transition"
      offset-y
      min-width="auto"
    >
      <template v-slot:activator="{ on, attrs }">
        <v-text-field
          v-model="dateStr"
          label="Fechas"
          readonly
          v-bind="attrs"
          v-on="on"
          dense
          hide-details
          class="font-weight-medium text-caption"
        >
        </v-text-field>
      </template>
      <v-date-picker v-model="dates" :max="new Date().toISOString().substr(0, 10)" range no-title scrollable>
          <v-spacer></v-spacer>
          <v-btn text color="primary" @click="menu = false">Cancel</v-btn>
          <v-btn text color="primary" @click="save" :disabled="dates.length < 2">OK</v-btn>
      </v-date-picker>
    </v-menu>
  </div>
</template>

<script>
import moment from "moment";

export default {
  props: ['module'],
  data() {
    return {
      menu: false,
      dates: [
        moment().format('YYYY-MM-DD'),
        moment().format('YYYY-MM-DD')
      ],
    }
  },
  methods: {
    save(){
      this.$refs.menu.save(this.dates)
      this.$store.commit(`${this.module}/SET_FILTRO_FECHA`, this.dates)
      this.$store.dispatch(`${this.module}/getFichas`, 1)
    },
  },
  computed: {
    dateStr() {
      return moment(this.dates[0]).format('DD/MM/YY') + ' ~ ' + moment(this.dates[1]).format('DD/MM/YY')
    },
  }
}
</script>
<template>
  <v-container>
        <v-btn color="#EF820F" fixed bottom right fab dark @click="CargarGrafico()"><v-icon>mdi-update</v-icon></v-btn>
    <v-row>
      <v-col cols="12" sm="12">
        <v-card elevation="12" class="mx-auto">
          <v-list-item three-line>
            <v-list-item-content>
              <v-list-item-title class="headline mb-1"
                >Indicador</v-list-item-title
              >
              <v-list-item-subtitle>TIempos Complejo</v-list-item-subtitle>
            </v-list-item-content>
          </v-list-item>
        <div id="chartdiv"></div>
        </v-card>
      </v-col>
    </v-row>
    
   <!-- <v-row mx-auto>
      <v-col cols="12" sm="12">
        <v-card elevation="12" class="mx-auto">
          <v-list-item three-line>
            <v-list-item-content>
              <v-list-item-title class="headline mb-1"
                >Indicador</v-list-item-title
              >
              <v-list-item-subtitle
                >Total por tipo de Prueba</v-list-item-subtitle
              >
            </v-list-item-content>
          </v-list-item>
          <div id="chartdiv2"></div>
        </v-card>
      </v-col>
    </v-row>-->
  </v-container>
</template>

<script>
import WorkflowChart from 'vue-workflow-chart';
import "viewerjs/dist/viewer.css";
import Viewer from "v-viewer/src/component.vue";
import { required } from "vee-validate/dist/rules";
import {
  extend,
  ValidationObserver,
  ValidationProvider,
  setInteractionMode,
} from "vee-validate";
import * as am4core from "@amcharts/amcharts4/core";
import * as am4charts from "@amcharts/amcharts4/charts";
import am4themes_animated from "@amcharts/amcharts4/themes/animated";
import * as am4plugins_timeline from "@amcharts/amcharts4/plugins/timeline";
import * as am4plugins_bullets from "@amcharts/amcharts4/plugins/bullets";
import am4themes_kelly from "@amcharts/amcharts4/themes/kelly";

am4core.useTheme(am4themes_animated);

setInteractionMode("eager");
extend("required", {
  ...required,
  message: "{field} can not be empty",
});
export default {
  components: {
    Viewer,
    ValidationObserver,
    ValidationProvider,
    WorkflowChart,

  },
  data() {
    return {
      campo_busqueda: "",
      currentPage: 1,
      lastPage: 0,
      pruebas: [],
      dialog: {
        reporte: false,
      },
      dates: [],
      images: [],
      arrayResultados: "",
      states: [{
            "id": "state_1",
            "label": "State 1",
        }, {
            "id": "state_2",
            "label": "State 2",
        }],
        transitions: [{
            "id": "transition_1",
            "label": "this is a transition",
            "target": "state_2",
            "source": "state_1",
        }],
    };
  },
  methods: {
    listarPruebasMoleculares(page, buscar) {
      axios
        .get("/listarPruebasMoleculares?page=" + page + "&buscar=" + buscar)
        .then((res) => {
          if (res) {
            this.pruebas = res.data.data;
            this.lastPage = res.data.last_page;
            console.log(this.pruebas);
          }
        })
        .catch((err) => {
          if (err) {
            console.log(err);
          }
        });
    },
    async positivosPorDia() {
      await axios.get("/tiemposComplejo").then((response) => {
        //var respuesta = response.data;
        this.arrayResultados = response.data.data;
        this.primero = response.data.primero;
        this.ultimo = response.data.ultimo;
        console.log(this.primero);
        console.log(this.ultimo);
      });
    },
    async positivosPorDiaChilina() {
      await axios.get("/tiemposChilina").then((response) => {
        //var respuesta = response.data;
        this.arrayResultados = response.data.data;
        this.primero = response.data.primero;
        this.ultimo = response.data.ultimo;
        console.log(this.primero);
        console.log(this.ultimo);
      });
    },
    async generarGrafico() {
      /* Chart code */
      // Themes begin

      // Themes end
      await this.positivosPorDiaChilina();
      let container = am4core.create("chartdiv2", am4core.Container);
      container.width = am4core.percent(100);
      container.height = am4core.percent(100);

      let interfaceColors = new am4core.InterfaceColorSet();
      let colorSet = new am4core.ColorSet();

      let chart = container.createChild(am4plugins_timeline.CurveChart);

      chart.data = this.arrayResultados.reverse();
      var dates = this.arrayResultados.map(function (x) {
        return new Date(x[4]);
      });

      chart.dateFormatter.dateFormat = "yyyy-MM-dd hh:mm";
      chart.dateFormatter.inputDateFormat = "yyyy-MM-dd hh:mm";
      chart.dy = 90;
      chart.maskBullets = false;

      let categoryAxis = chart.yAxes.push(new am4charts.CategoryAxis());
      categoryAxis.dataFields.category = "task";
      categoryAxis.renderer.labels.template.paddingRight = 25;
      categoryAxis.renderer.minGridDistance = 10;
      categoryAxis.renderer.innerRadius = 0;
      categoryAxis.renderer.radius = 200;
      categoryAxis.renderer.grid.template.location = 1;

      let dateAxis = chart.xAxes.push(new am4charts.DateAxis());
      dateAxis.renderer.minGridDistance = 70;
      dateAxis.min = new Date(this.primero.start).getTime();
      dateAxis.max = new Date(this.ultimo.start).getTime();

      dateAxis.baseInterval = { count: 1, timeUnit: "minute" };
      dateAxis.startLocation = -0.5;

      dateAxis.renderer.points = [
        { x: -400, y: 0 },
        { x: -250, y: 0 },
        { x: 0, y: 60 },
        { x: 250, y: 0 },
        { x: 400, y: 0 },
      ];
      dateAxis.renderer.autoScale = false;
      dateAxis.renderer.polyspline.tensionX = 0.8;
      dateAxis.renderer.tooltipLocation = 0;
      dateAxis.renderer.grid.template.disabled = true;
      dateAxis.renderer.line.strokeDasharray = "1,4";
      dateAxis.renderer.line.strokeOpacity = 0.7;
      dateAxis.tooltip.background.fillOpacity = 0.2;
      dateAxis.tooltip.background.cornerRadius = 5;
      dateAxis.tooltip.label.fill = new am4core.InterfaceColorSet().getFor(
        "alternativeBackground"
      );
      dateAxis.tooltip.label.paddingTop = 7;

      let labelTemplate = dateAxis.renderer.labels.template;
      labelTemplate.verticalCenter = "middle";
      labelTemplate.fillOpacity = 0.7;
      labelTemplate.background.fill = interfaceColors.getFor("background");
      labelTemplate.background.fillOpacity = 1;
      labelTemplate.padding(7, 7, 7, 7);

      let series = chart.series.push(
        new am4plugins_timeline.CurveColumnSeries()
      );
      series.columns.template.height = am4core.percent(15);
      series.columns.template.tooltipText =
        "{categoryX}: [bold]{openDateX}[/] - [bold]{dateX}[/]";

      series.dataFields.openDateX = "start";
      series.dataFields.dateX = "end";
      series.dataFields.categoryY = "task";
      series.columns.template.propertyFields.fill = "color"; // get color from data
      series.columns.template.propertyFields.stroke = "color";
      series.columns.template.strokeOpacity = 0;

      series.columns.template.adapter.add("fill", function (fill, target) {
        return chart.colors.getIndex(target.dataItem.index * 3);
      });

      let flagBullet1 = new am4plugins_bullets.FlagBullet();
      series.bullets.push(flagBullet1);
      flagBullet1.disabled = true;
      flagBullet1.propertyFields.disabled = "bulletf1";
      flagBullet1.locationX = 1;
      flagBullet1.label.text = "start";

      let flagBullet2 = new am4plugins_bullets.FlagBullet();
      series.bullets.push(flagBullet2);
      flagBullet2.disabled = true;
      flagBullet2.propertyFields.disabled = "bulletf2";
      flagBullet2.locationX = 0;
      flagBullet2.background.fill = interfaceColors.getFor("background");
      flagBullet2.label.text = "end";

      let bullet = new am4charts.CircleBullet();
      series.bullets.push(bullet);
      bullet.circle.radius = 3;
      bullet.circle.strokeOpacity = 0;
      bullet.locationX = 0;

      bullet.adapter.add("fill", function (fill, target) {
        return chart.colors.getIndex(target.dataItem.index * 3);
      });

      let bullet2 = new am4charts.CircleBullet();
      series.bullets.push(bullet2);
      bullet2.circle.radius = 3;
      bullet2.circle.strokeOpacity = 0;
      bullet2.propertyFields.fill = "color";
      bullet2.locationX = 1;

      bullet2.adapter.add("fill", function (fill, target) {
        return chart.colors.getIndex(target.dataItem.index * 3);
      });

      chart.scrollbarX = new am4core.Scrollbar();
      chart.scrollbarX.align = "center";
      chart.scrollbarX.width = 800;
      chart.scrollbarX.parent = chart.bottomAxesContainer;
      chart.scrollbarX.dy = -90;
      chart.scrollbarX.opacity = 0.4;

      let cursor = new am4plugins_timeline.CurveCursor();
      chart.cursor = cursor;
      cursor.xAxis = dateAxis;
      cursor.yAxis = categoryAxis;
      cursor.lineY.disabled = true;
      cursor.lineX.strokeDasharray = "1,4";
      cursor.lineX.strokeOpacity = 1;

      dateAxis.renderer.tooltipLocation2 = 0;
      categoryAxis.cursorTooltipEnabled = false;

      /// clock
      let clock = container.createChild(am4charts.GaugeChart);
      clock.toBack();

      clock.radius = 120;
      clock.dy = -150;
      clock.startAngle = -90;
      clock.endAngle = 270;

      let axis = clock.xAxes.push(new am4charts.ValueAxis());
      axis.min = 0;
      axis.max = 12;
      axis.strictMinMax = true;

      axis.renderer.line.strokeWidth = 1;
      axis.renderer.line.strokeOpacity = 0.5;
      axis.renderer.line.strokeDasharray = "1,4";
      axis.renderer.minLabelPosition = 0.05; // hides 0 label
      axis.renderer.inside = true;
      axis.renderer.labels.template.radius = 30;
      axis.renderer.grid.template.disabled = true;
      axis.renderer.ticks.template.length = 12;
      axis.renderer.ticks.template.strokeOpacity = 1;

      // serves as a clock face fill
      let range = axis.axisRanges.create();
      range.value = 0;
      range.endValue = 12;
      range.grid.visible = false;
      range.tick.visible = false;
      range.label.visible = false;

      let axisFill = range.axisFill;

      // hands
      let hourHand = clock.hands.push(new am4charts.ClockHand());
      hourHand.radius = am4core.percent(60);
      hourHand.startWidth = 5;
      hourHand.endWidth = 5;
      hourHand.rotationDirection = "clockWise";
      hourHand.pin.radius = 8;
      hourHand.zIndex = 1;

      let minutesHand = clock.hands.push(new am4charts.ClockHand());
      minutesHand.rotationDirection = "clockWise";
      minutesHand.startWidth = 3;
      minutesHand.endWidth = 3;
      minutesHand.radius = am4core.percent(78);
      minutesHand.zIndex = 2;

      chart.cursor.events.on("cursorpositionchanged", function (event) {
        let value = dateAxis.positionToValue(event.target.xPosition);
        let date = new Date(value);
        let hours = date.getHours();
        let minutes = date.getMinutes();
        // set hours
        hourHand.showValue(hours + minutes / 60, 0);
        // set minutes
        minutesHand.showValue((12 * minutes) / 60, 0);
      });

      let button = chart.chartContainer.createChild(am4core.Button);
      button.label.text = "...";
      button.padding(5, 5, 5, 5);
      button.width = 50;
      button.align = "right";
      button.marginRight = 15;
      button.events.on("hit", function () {
        chart.goHome();
      });
    },
    async generarGrafico2() {
     await this.positivosPorDia();
      let container = am4core.create("chartdiv", am4core.Container);
      container.width = am4core.percent(100);
      container.height = am4core.percent(100);

      let interfaceColors = new am4core.InterfaceColorSet();
      let colorSet = new am4core.ColorSet();

      let chart = container.createChild(am4plugins_timeline.CurveChart);

      chart.data = this.arrayResultados.reverse();
      var dates = this.arrayResultados.map(function (x) {
        return new Date(x[4]);
      });

      chart.dateFormatter.dateFormat = "yyyy-MM-dd hh:mm";
      chart.dateFormatter.inputDateFormat = "yyyy-MM-dd hh:mm";
      chart.dy = 90;
      chart.maskBullets = false;

      let categoryAxis = chart.yAxes.push(new am4charts.CategoryAxis());
      categoryAxis.dataFields.category = "task";
      categoryAxis.renderer.labels.template.paddingRight = 25;
      categoryAxis.renderer.minGridDistance = 10;
      categoryAxis.renderer.innerRadius = 0;
      categoryAxis.renderer.radius = 200;
      categoryAxis.renderer.grid.template.location = 1;

      let dateAxis = chart.xAxes.push(new am4charts.DateAxis());
      dateAxis.renderer.minGridDistance = 70;
      dateAxis.min = new Date(this.primero.start).getTime();
      dateAxis.max = new Date(this.ultimo.start).getTime();

      dateAxis.baseInterval = { count: 1, timeUnit: "minute" };
      dateAxis.startLocation = -0.5;

      dateAxis.renderer.points = [
        { x: -400, y: 0 },
        { x: -250, y: 0 },
        { x: 0, y: 60 },
        { x: 250, y: 0 },
        { x: 400, y: 0 },
      ];
      dateAxis.renderer.autoScale = false;
      dateAxis.renderer.polyspline.tensionX = 0.8;
      dateAxis.renderer.tooltipLocation = 0;
      dateAxis.renderer.grid.template.disabled = true;
      dateAxis.renderer.line.strokeDasharray = "1,4";
      dateAxis.renderer.line.strokeOpacity = 0.7;
      dateAxis.tooltip.background.fillOpacity = 0.2;
      dateAxis.tooltip.background.cornerRadius = 5;
      dateAxis.tooltip.label.fill = new am4core.InterfaceColorSet().getFor(
        "alternativeBackground"
      );
      dateAxis.tooltip.label.paddingTop = 7;

      let labelTemplate = dateAxis.renderer.labels.template;
      labelTemplate.verticalCenter = "middle";
      labelTemplate.fillOpacity = 0.7;
      labelTemplate.background.fill = interfaceColors.getFor("background");
      labelTemplate.background.fillOpacity = 1;
      labelTemplate.padding(7, 7, 7, 7);

      let series = chart.series.push(
        new am4plugins_timeline.CurveColumnSeries()
      );
      series.columns.template.height = am4core.percent(15);
      series.columns.template.tooltipText =
        "{categoryX}: [bold]{openDateX}[/] - [bold]{dateX}[/]";

      series.dataFields.openDateX = "start";
      series.dataFields.dateX = "end";
      series.dataFields.categoryY = "task";
      series.columns.template.propertyFields.fill = "color"; // get color from data
      series.columns.template.propertyFields.stroke = "color";
      series.columns.template.strokeOpacity = 0;

      series.columns.template.adapter.add("fill", function (fill, target) {
        return chart.colors.getIndex(target.dataItem.index * 3);
      });

      let flagBullet1 = new am4plugins_bullets.FlagBullet();
      series.bullets.push(flagBullet1);
      flagBullet1.disabled = true;
      flagBullet1.propertyFields.disabled = "bulletf1";
      flagBullet1.locationX = 1;
      flagBullet1.label.text = "start";

      let flagBullet2 = new am4plugins_bullets.FlagBullet();
      series.bullets.push(flagBullet2);
      flagBullet2.disabled = true;
      flagBullet2.propertyFields.disabled = "bulletf2";
      flagBullet2.locationX = 0;
      flagBullet2.background.fill = interfaceColors.getFor("background");
      flagBullet2.label.text = "end";

      let bullet = new am4charts.CircleBullet();
      series.bullets.push(bullet);
      bullet.circle.radius = 3;
      bullet.circle.strokeOpacity = 0;
      bullet.locationX = 0;

      bullet.adapter.add("fill", function (fill, target) {
        return chart.colors.getIndex(target.dataItem.index * 3);
      });

      let bullet2 = new am4charts.CircleBullet();
      series.bullets.push(bullet2);
      bullet2.circle.radius = 3;
      bullet2.circle.strokeOpacity = 0;
      bullet2.propertyFields.fill = "color";
      bullet2.locationX = 1;

      bullet2.adapter.add("fill", function (fill, target) {
        return chart.colors.getIndex(target.dataItem.index * 3);
      });

      chart.scrollbarX = new am4core.Scrollbar();
      chart.scrollbarX.align = "center";
      chart.scrollbarX.width = 800;
      chart.scrollbarX.parent = chart.bottomAxesContainer;
      chart.scrollbarX.dy = -90;
      chart.scrollbarX.opacity = 0.4;

      let cursor = new am4plugins_timeline.CurveCursor();
      chart.cursor = cursor;
      cursor.xAxis = dateAxis;
      cursor.yAxis = categoryAxis;
      cursor.lineY.disabled = true;
      cursor.lineX.strokeDasharray = "1,4";
      cursor.lineX.strokeOpacity = 1;

      dateAxis.renderer.tooltipLocation2 = 0;
      categoryAxis.cursorTooltipEnabled = false;

      /// clock
      let clock = container.createChild(am4charts.GaugeChart);
      clock.toBack();

      clock.radius = 120;
      clock.dy = -150;
      clock.startAngle = -90;
      clock.endAngle = 270;

      let axis = clock.xAxes.push(new am4charts.ValueAxis());
      axis.min = 0;
      axis.max = 12;
      axis.strictMinMax = true;

      axis.renderer.line.strokeWidth = 1;
      axis.renderer.line.strokeOpacity = 0.5;
      axis.renderer.line.strokeDasharray = "1,4";
      axis.renderer.minLabelPosition = 0.05; // hides 0 label
      axis.renderer.inside = true;
      axis.renderer.labels.template.radius = 30;
      axis.renderer.grid.template.disabled = true;
      axis.renderer.ticks.template.length = 12;
      axis.renderer.ticks.template.strokeOpacity = 1;

      // serves as a clock face fill
      let range = axis.axisRanges.create();
      range.value = 0;
      range.endValue = 12;
      range.grid.visible = false;
      range.tick.visible = false;
      range.label.visible = false;

      let axisFill = range.axisFill;

      // hands
      let hourHand = clock.hands.push(new am4charts.ClockHand());
      hourHand.radius = am4core.percent(60);
      hourHand.startWidth = 5;
      hourHand.endWidth = 5;
      hourHand.rotationDirection = "clockWise";
      hourHand.pin.radius = 8;
      hourHand.zIndex = 1;

      let minutesHand = clock.hands.push(new am4charts.ClockHand());
      minutesHand.rotationDirection = "clockWise";
      minutesHand.startWidth = 3;
      minutesHand.endWidth = 3;
      minutesHand.radius = am4core.percent(78);
      minutesHand.zIndex = 2;

      chart.cursor.events.on("cursorpositionchanged", function (event) {
        let value = dateAxis.positionToValue(event.target.xPosition);
        let date = new Date(value);
        let hours = date.getHours();
        let minutes = date.getMinutes();
        // set hours
        hourHand.showValue(hours + minutes / 60, 0);
        // set minutes
        minutesHand.showValue((12 * minutes) / 60, 0);
      });

      let button = chart.chartContainer.createChild(am4core.Button);
      button.label.text = "...";
      button.padding(5, 5, 5, 5);
      button.width = 50;
      button.align = "right";
      button.marginRight = 15;
      button.events.on("hit", function () {
        chart.goHome();
      });
    },
    generarReporte(fechas) {
      const inicio = fechas[0];
      const final = fechas[1];
      this.$refs.observer.validate().then((valid) => {
        if (valid) {
          window.open("/exportnuevo_pcr/" + inicio + "/" + final, "_blank");
        }
      });
    },
    CargarGrafico() {
     this.generarGrafico();
    },
    verFotoFI(path) {
      this.images = [];
      this.$viewer.show();
      this.images.push("/downloadFileFI/" + path);
    },
    inited(viewer) {
      this.$viewer = viewer;
    },
    show() {
      this.$viewer.show();
    },
  },
  mounted() {
    //this.listarPruebasMoleculares(0, this.campo_busqueda);
    this.generarGrafico();
    this.generarGrafico2();
  },
  watch: {
    currentPage(newVal) {
      this.currentPage = newVal;
      this.listarPruebasMoleculares(this.currentPage, this.campo_busqueda);
    },
  },
  computed: {
    dateRangeText() {
      return this.dates.join(" ~ ");
    },
  },
};
</script>
<style>
#chartdiv {
  width: 100%;
  height: 600px;
}
#chartdiv2 {
  width: 100%;
  height: 600px;
}
</style>



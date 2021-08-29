<template>
    <v-container>
        <v-btn color="#EF820F" fixed bottom right fab dark @click="dialog = 1"
            ><v-icon>mdi-update</v-icon></v-btn
        >
        <v-dialog v-model="dialog" persistent max-width="600px">
            <v-card>
                <v-card-title>
                    <span class="headline">Seleccionar Fechas</span>
                </v-card-title>
                <v-card-text>
                    <v-container>
                        <v-row>
                            <v-col cols="8">
                                <v-date-picker
                                    v-model="picker"
                                    year-icon="mdi-calendar-blank"
                                    prev-icon="mdi-skip-previous"
                                    next-icon="mdi-skip-next"
                                    locale="es-es"
                                ></v-date-picker>
                                <!-- <v-date-picker
                                    v-model="dates"
                                    range
                                ></v-date-picker>-->
                            </v-col>

                            <v-col cols="4">
                                <!--<v-text-field
                                    v-model="dateRangeText"
                                    label="Date range"
                                    prepend-icon="mdi-calendar"
                                    readonly
                                ></v-text-field>-->
                                <v-btn
                                    class="ma-2"
                                    color="light-blue darken-4"
                                    dark
                                    @click="generarGraficos()"
                                >
                                    Generar
                                </v-btn>
                            </v-col>
                        </v-row>
                    </v-container>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue darken-1" text @click="dialog = false">
                        Cerrar
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <v-row>
            <v-col cols="12" sm="12">
                <v-card elevation="12" class="mx-auto">
                    <v-list-item three-line>
                        <v-list-item-content>
                            <v-list-item-title class="headline mb-1"
                                >Indicador</v-list-item-title
                            >
                            <v-list-item-subtitle
                                >Rendimiento de Estaciones por Tipo de
                                Prueba</v-list-item-subtitle
                            >
                        </v-list-item-content>
                    </v-list-item>
                    <div id="chartdiv"></div>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script>
import * as am4core from "@amcharts/amcharts4/core";
import * as am4charts from "@amcharts/amcharts4/charts";
import am4themes_kelly from "@amcharts/amcharts4/themes/kelly";
import am4themes_animated from "@amcharts/amcharts4/themes/animated";

am4core.useTheme(am4themes_animated);

export default {
    data() {
        return {
            campo_busqueda: "",
            picker: new Date(
                Date.now() - new Date().getTimezoneOffset() * 60000
            )
                .toISOString()
                .substr(0, 10),
            currentPage: 1,
            lastPage: 0,
            pruebas: [],
            dialog: 0,

            dates: [],
            images: [],
            arrayResultados: "",
            states: [
                {
                    id: "state_1",
                    label: "State 1"
                },
                {
                    id: "state_2",
                    label: "State 2"
                }
            ],
            transitions: [
                {
                    id: "transition_1",
                    label: "this is a transition",
                    target: "state_2",
                    source: "state_1"
                }
            ]
        };
    },
    methods: {
        generarGraficos() {
            this.dialog = false;
            this.generarGrafico();
            console.log(this.picker);        },
        async PruebasPorEstacion() {
            // let me = this;
            await axios.get("/totalBarrasComplejo/"+this.picker).then(response => {
                var respuesta = response.data;

                /*var result = Object.assign(
                    {},
                    Object.values(respuesta.resultadoscv).concat(Object.values(respuesta.resultadospar))
                );
                console.log(result);*/

                var result = _.map(respuesta.serologicas, function(obj) {
                    return _.assign(
                        obj,
                        _.find(respuesta.moleculares, {
                            year: obj.year
                        })
                    );
                });
                /* var result2 = _.map(result, function(obj) {
                        return _.assign(
                            obj,
                            _.find(respuesta.resultadosasum, {
                                semana: obj.semana
                            })
                        );
                    });*/
                this.arrayBarrasEstacion = result;
                this.totalmoleculares = respuesta["molecularestotal"];
                this.totalserologicas = respuesta["serologicastotal"];
                console.log(this.totalserologicas);
            });
        },
        async PruebasPorEstacionInicial() {
            // let me = this;
            await axios.get("/totalBarrasComplejoInicial/"+this.picker).then(response => {
                var respuesta = response.data;

                /*var result = Object.assign(
                    {},
                    Object.values(respuesta.resultadoscv).concat(Object.values(respuesta.resultadospar))
                );
                console.log(result);*/

                var result = _.map(respuesta.serologicas, function(obj) {
                    return _.assign(
                        obj,
                        _.find(respuesta.moleculares, {
                            year: obj.year
                        })
                    );
                });
                /* var result2 = _.map(result, function(obj) {
                        return _.assign(
                            obj,
                            _.find(respuesta.resultadosasum, {
                                semana: obj.semana
                            })
                        );
                    });*/
                this.arrayBarrasEstacionInicial = result;
                // console.log(this.arrayBarrasEstacionInicial);
            });
        },
        async generarGrafico() {
            /* Chart code */
            // Themes begin
            am4core.useTheme(am4themes_kelly);
            am4core.useTheme(am4themes_animated);
            // Themes end
            await this.PruebasPorEstacionInicial();
            //console.log(this.arrayBarrasCV);

            // Add data

            // Create chart instance
            let chart = am4core.create("chartdiv", am4charts.XYChart);

            if (this.arrayBarrasEstacionInicial.length == 0) {
                this.arrayBarrasEstacionInicial = [
                    {
                        year: "0",
                        europe: 0
                    }
                ];
            }
            //console.log(this.totalComplejo);
            chart.data = this.arrayBarrasEstacionInicial;

            let topContainer = chart.chartContainer.createChild(
                am4core.Container
            );
            topContainer.layout = "absolute";
            topContainer.toBack();
            topContainer.paddingBottom = 15;
            topContainer.width = am4core.percent(100);
            let dateTitle = topContainer.createChild(am4core.Label);
            dateTitle.text = "PRS:-/PCR:-";
            dateTitle.fontWeight = 800;
            dateTitle.align = "right";
            // Add data

            // Create axes
            let categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
            categoryAxis.dataFields.category = "year";
            categoryAxis.renderer.minGridDistance = 50;
            categoryAxis.renderer.grid.template.location = 0.5;

            let valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
            valueAxis.renderer.inside = true;
            valueAxis.renderer.labels.template.disabled = true;
            valueAxis.min = 0;

            // Create series
            function createSeries(field, name) {
                // Set up series
                let series = chart.series.push(new am4charts.ColumnSeries());
                series.name = name;
                series.dataFields.valueY = field;
                series.dataFields.categoryX = "year";
                series.sequencedInterpolation = true;

                // Make it stacked
                series.stacked = true;

                // Configure columns
                series.columns.template.width = am4core.percent(80);
                series.columns.template.tooltipText =
                    "[bold]{name}[/]\n[font-size:14px]{categoryX}: {valueY}";

                // Add label
                let labelBullet = series.bullets.push(
                    new am4charts.LabelBullet()
                );
                labelBullet.label.text = "{valueY}";
                labelBullet.locationY = 0.5;
                labelBullet.label.hideOversized = true;

                return series;
            }

            var resta;
            var resta2;
            let me = this;
            setInterval(function() {
                //dateTitle.text = "PRS:"+ this.totalserologicas + "/"+ "PCR:"+ this.totalmoleculares;
                dateTitle.text =
                    "PRS:" +
                    me.totalserologicas +
                    "/PCR:" +
                    me.totalmoleculares;
                am4core.array.each(chart.data, async function(item) {
                    //item.visits += Math.round(Math.random() * 200 - 100);
                    let obj = me.arrayBarrasEstacion.find(
                        o => o.year === item.year
                    );
                    //
                    if (typeof obj !== "undefined") {
                        resta = obj.europe - item.europe;
                        //console.log(resta);
                        item.europe += resta;
                        resta2 = obj.namerica - item.namerica;
                        //console.log(resta2);
                        item.namerica += resta2;
                    }
                });
                chart.invalidateRawData();
            }, 5000);
            createSeries("europe", "PRS");
            createSeries("namerica", "PCR");

            // Legend
            chart.legend = new am4charts.Legend();
        }
    },
    async created() {
        //this.interval = setInterval(() => this.positivosPorDia(), 5000);
        this.interval = await setInterval(
            () => this.PruebasPorEstacion(),
            5000
        );
        //this.interval = setInterval(() => this.totalPacientes(), 5000);
    },
    async mounted() {
        //this.listarPruebasMoleculares(0, this.campo_busqueda);
        this.PruebasPorEstacionInicial();
        this.generarGrafico();
    },
    watch: {
        currentPage(newVal) {
            this.currentPage = newVal;
            this.listarPruebasMoleculares(
                this.currentPage,
                this.campo_busqueda
            );
        }
    },
    computed: {
        dateRangeText() {
            return this.dates.join(" ~ ");
        }
    }
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

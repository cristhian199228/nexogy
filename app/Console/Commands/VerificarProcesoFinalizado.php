<?php

namespace App\Console\Commands;

use App\Exports\ExcelPcrCVExport;
use App\Mail\MailEnvioPcrCV;
use App\Mail\MailProcesoFinalizadoCV;
use App\PcrPruebaMolecular;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class VerificarProcesoFinalizado extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verificar:pcrcv_fin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificacion de proceso terminado PCR Cerro Verde';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $fechaActual = date('Y-m-d');
        $fechaAnterior = date('Y-m-d',strtotime("-1 days"));

        $hora = date('H');

        if ($hora >= 17 || $hora <= 6) {

            if($hora >= 18) {

                $total = PcrPruebaMolecular::where('fecha',$fechaActual)
                    ->where('enviado_ws',1)
                    ->whereHas('FichaPaciente', function ($q) {
                        $q->where('id_estacion','<>', 32);
                    })
                    ->count();

                $nro_procesados = PcrPruebaMolecular::where('fecha',$fechaActual)
                    ->where('enviado_ws',1)
                    ->whereNotNull('resultado')
                    ->whereHas('FichaPaciente', function ($q) {
                        $q->where('id_estacion','<>', 32);
                    })
                    ->count();

                $positivos_cv = PcrPruebaMolecular::where('fecha',$fechaActual)
                    ->whereHas('FichaPaciente.Empresa', function ($q) {
                        $q->where('empresa.idempresa',7);
                    })
                    ->where('resultado', 1)
                    ->whereHas('FichaPaciente', function ($q) {
                        $q->where('id_estacion','<>', 32);
                    })
                    ->with('FichaPaciente.Estacion.Sede')
                    ->with('FichaPaciente.Empresa')
                    ->get();

                $paciente_referencia = PcrPruebaMolecular::where('fecha',$fechaActual)
                    ->first();

            } else {

                $total = PcrPruebaMolecular::whereBetween('fecha',[$fechaAnterior,$fechaActual])
                    ->where('enviado_ws',1)
                    ->whereHas('FichaPaciente', function ($q) {
                        $q->where('id_estacion','<>', 32);
                    })
                    ->count();

                $nro_procesados = PcrPruebaMolecular::whereBetween('fecha',[$fechaAnterior,$fechaActual])
                    ->where('enviado_ws',1)
                    ->whereNotNull('resultado')
                    ->whereHas('FichaPaciente', function ($q) {
                        $q->where('id_estacion','<>', 32);
                    })
                    ->count();

                $positivos_cv = PcrPruebaMolecular::whereBetween('fecha',[$fechaAnterior,$fechaActual])
                    ->whereHas('FichaPaciente.Empresa', function ($q) {
                        $q->where('empresa.idempresa',7);
                    })
                    ->where('resultado', 1)
                    ->whereHas('FichaPaciente', function ($q) {
                        $q->where('id_estacion','<>', 32);
                    })
                    ->with('FichaPaciente.Estacion.Sede')
                    ->with('FichaPaciente.Empresa')
                    ->get();

                $paciente_referencia = PcrPruebaMolecular::whereBetween('fecha',[$fechaAnterior,$fechaActual])
                    ->first();

            }
        }

        $nro_positivos = $positivos_cv->count();
        $fecha_referencia = $paciente_referencia->fecha;

        foreach ($positivos_cv as $prueba) {
            if(!is_null($prueba->resultado)) {
                if($prueba->resultado === 0) {
                    $prueba->resultadoPM = "NEGATIVO";
                } else if($prueba->resultado === 1) {
                    $prueba->resultadoPM = "POSITIVO";
                } else {
                    $prueba->resultadoPM = "";
                }
            } else {
                $prueba->resultadoPM = "";
            }
        }

        $destinatarios = array('fzegarra@fmi.com','eponcede@fmi.com','illerena@fmi.com','lgrajeda@fmi.com','tcarpiog@fmi.com','ssufling@fmi.com',
            'pjimenez@fmi.com','rzunigaz@fmi.com','amoriavi@fmi.com','lgarciat@fmi.com','jparedes@fmi.com','gyanezga@fmi.com','rmolinaa@fmi.com',
            'juan.sanchez@internationalsos.com','juan.sanchez@isos-peru.com','luis.ampuero@isos-peru.com','marco.bautista@isos-peru.com','elard.laura@isos-peru.com',
            'alberto.flores@isos-peru.com','responsecenter@isos-peru.com','medicoasistencial.p1aqp@isos-peru.com','enfermeriaasistencial.p1aqp@isos-peru.com',
            'saludocupacional.p1aqp@isos-peru.com','fsalcedo@fmi.com','karen.daly@internationalsos.com','eriverar@fmi.com','npacheco@fmi.com','jquispea2@fmi.com');

        $copia = array('cristhian.vargas@isos-peru.com','samuel.larico@isos-peru.com',
            'ramiro.linares@isos-peru.com','aldair.duran@isos-peru.com','yadira.alfaro@isos-peru.com');

        /*$destinatarios = array('samuel.larico@isos-peru.com');

        $copia = array('cristhian.vargas@isos-peru.com');*/

        if($total > 0) {

            $excel_rc = Excel::store(new ExcelPcrCVExport($positivos_cv), 'excel/resultados_pcr_cv.xlsx');
            Mail::to($destinatarios)
                ->cc($copia)
                ->send(new MailProcesoFinalizadoCV($excel_rc, $total, $nro_procesados, $nro_positivos, $fecha_referencia));
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Exports\ExcelPcrCVExport;
use App\Exports\NuevoResponceCenterPcrExport;
use App\FichaPaciente;
use App\Mail\EnvioPcrCV;
use App\Mail\MailEnvioPcrCV;
use App\Mail\MailEnvioPcrCVvacio;
use App\Mail\ResponceCenterPcrMail;
use App\PcrPruebaMolecular;
use App\PruebaSerologica;
use App\Service\PacienteService;
use App\Service\PruebaMolecularService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class EnviarExcelPcrCV extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enviar:excel_pcr_cv';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'MAIL RESULTADOS POSITIVOS PCR PARA CERRO VERDE';

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

        $total = FichaPaciente::when($hora >= 18, function ($q) use ($fechaActual){
                return $q->where(DB::raw('DATE(created_at)'), $fechaActual);
            }, function ($q) use ($fechaActual, $fechaAnterior) {
                return $q->whereBetween(DB::raw('DATE(created_at)'), [$fechaAnterior, $fechaActual]);
            })
            ->whereHas('Estacion', function (Builder $q) {
                $q->whereIn('idsede', [1, 2, 3, 4, 6]);
            })
            ->whereHas('PacienteIsos')
            ->whereHas('PcrPruebaMolecular')
            ->count();

        $nro_procesados = FichaPaciente::when($hora >= 18, function ($q) use ($fechaActual){
                return $q->where(DB::raw('DATE(created_at)'), $fechaActual);
            }, function ($q) use ($fechaActual, $fechaAnterior) {
                return $q->whereBetween(DB::raw('DATE(created_at)'), [$fechaAnterior, $fechaActual]);
            })
            ->whereHas('Estacion', function (Builder $q) {
                $q->whereIn('idsede', [1, 2, 3, 4, 6]);
            })
            ->whereHas('PacienteIsos')
            ->whereHas('PcrPruebaMolecular', function ($q) {
                $q->whereNotNull('resultado');
            })
            ->count();


        $fichas = FichaPaciente::when($hora >= 18, function ($q) use ($fechaActual){
                return $q->where(DB::raw('DATE(created_at)'), $fechaActual);
            }, function ($q) use ($fechaActual, $fechaAnterior) {
                return $q->whereBetween(DB::raw('DATE(created_at)'), [$fechaAnterior, $fechaActual]);
            })
            ->whereHas('Estacion', function (Builder $q) {
                $q->whereIn('idsede', [1, 2, 3, 4, 6]);
            })
            ->whereHas('PacienteIsos', function ($q) {
                $q->where('idempresa', 7);
            })
            ->with(["PacienteIsos" => function($q) {
                $q->select('idpacientes','nombres','apellido_paterno','apellido_materno','idempresa','numero_documento')
                    ->with("Empresa:idempresa,descripcion");
            }])
            ->whereHas('PcrPruebaMolecular', function ($q) {
                $q->where('resultado', 1);
            })
            ->with('PcrPruebaMolecular','Estacion.Sede')
            ->latest()
            ->get();

        $paciente_referencia = FichaPaciente::when($hora >= 18, function ($q) use ($fechaActual){
                return $q->where(DB::raw('DATE(created_at)'), $fechaActual);
            }, function ($q) use ($fechaActual, $fechaAnterior) {
                return $q->whereBetween(DB::raw('DATE(created_at)'), [$fechaAnterior, $fechaActual]);
            })
            ->whereHas('Estacion', function (Builder $q) {
                $q->whereIn('idsede', [1, 2, 3, 4, 6]);
            })
            ->whereHas('PacienteIsos')
            ->whereHas('PcrPruebaMolecular')
            ->first();

        $nro_positivos = $fichas->count();
        $fecha_referencia = Carbon::parse($paciente_referencia->created_at)->format('Y-m-d');
        $fecha = Carbon::parse($paciente_referencia->created_at)->format('dmY');

        $fichas = $fichas->filter(function ($ficha) {
            $service = new PacienteService($ficha->PacienteIsos);
            return !$service->esPositivoPcrTerceraVez();
        })->values();

        foreach ($fichas as $ficha) {
            $pac = $ficha->PacienteIsos;
            $ficha->fecha = Carbon::parse($ficha->created_at)->format('d-m-Y');
            $pac->nom_completo = $pac->nombres. " " . $pac->apellido_paterno. " " .$pac->apellido_materno;
            $ficha->turno = getStrTurno($ficha->turno);
            $ficha->rol = getStrRol($ficha->rol);
            $ficha->PcrPruebaMolecular->resultado = getStrPcrResult($ficha->PcrPruebaMolecular->resultado);
        }

        $destinatarios = array('fzegarra@fmi.com','eponcede@fmi.com','illerena@fmi.com','lgrajeda@fmi.com','tcarpiog@fmi.com','ssufling@fmi.com',
            'pjimenez@fmi.com','rzunigaz@fmi.com','lgarciat@fmi.com','jparedes@fmi.com','gyanezga@fmi.com','rmolinaa@fmi.com',
            'juan.sanchez@internationalsos.com','juan.sanchez@isos-peru.com','luis.ampuero@isos-peru.com','marco.bautista@isos-peru.com','elard.laura@isos-peru.com',
            'alberto.flores@isos-peru.com','responsecenter@isos-peru.com','medicoasistencial.p1aqp@isos-peru.com','enfermeriaasistencial.p1aqp@isos-peru.com',
            'saludocupacional.p1aqp@isos-peru.com','fsalcedo@fmi.com','eriverar@fmi.com','npacheco@fmi.com','jquispea2@fmi.com', 'cristina.garcia@internationalsos.com',
            'gbejaran@fmi.com', 'aotazuvi@fmi.com', 'jportale@fmi.com', 'jarguell@fmi.com', 'rpalzaga@fmi.com', 'obellido@fmi.com', 'jescuder@fmi.com',
            'jguillen@fmi.com', 'jhidalgo@fmi.com','vzamallo@fmi.com','pperalta1@fmi.com','jcaveroa@fmi.com');

        $copia = array('cristhian.vargas@isos-peru.com','samuel.larico@isos-peru.com',
            'ramiro.linares@isos-peru.com','alejandra.bustamante@isos-peru.com','aldair.duran@isos-peru.com','yadira.alfaro@isos-peru.com');

        /*$destinatarios = array('samuel.larico@isos-peru.com');

        $copia = array('samuel.larico@isos-peru.com');*/

        //dd($fichas);

        if($total > 0) {

            $excel_rc = Excel::store(new ExcelPcrCVExport($fichas), 'excel/resultados_pcr_cv.xlsx');
            Mail::to($destinatarios)
                ->cc($copia)
                ->send(new MailEnvioPcrCV($excel_rc, $total, $nro_procesados, $nro_positivos, $fecha_referencia));
        }

        return 0;
    }
}

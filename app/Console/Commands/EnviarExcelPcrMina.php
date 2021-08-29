<?php

namespace App\Console\Commands;

use App\Exports\ExcelPcrContratistasExport;
use App\Exports\ExportExcelPcrMina;
use App\FichaPaciente;
use App\Mail\MailEnvioPcrContratistas;
use App\Mail\MailEnvioPcrMina;
use App\Service\PacienteService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class EnviarExcelPcrMina extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enviar:excel_pcr_mina';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $dias = [2, 3, 4, 2, 2, 2, 2];
        $fechaActual = now()->format('Y-m-d');
        $fechaAnterior = now()->subDays($dias[now()->dayOfWeek])->format('Y-m-d');

        $fichas = FichaPaciente::whereBetween(DB::raw('DATE(created_at)'), [$fechaAnterior, $fechaActual])
            ->whereHas('Estacion', function (Builder $q) {
                $q->where('idsede', 4);
            })
            ->whereHas('PcrPruebaMolecular', function (Builder $q) {
                $q->whereNotNull('resultado');
            })
            ->with('PcrPruebaMolecular', 'Estacion.Sede', 'PacienteIsos.Empresa')
            ->latest()
            ->get();

        //dd($fichas->count());

        $fichas = $fichas->filter(function ($ficha) {
            $service = new PacienteService($ficha->PacienteIsos);
            return !$service->esPositivoPcrTerceraVez();
        })->values();

        foreach ($fichas as $ficha) {
            $ficha->horario_atencion = $ficha->created_at->format('H:i');
            $ficha->turno = getStrTurno($ficha->turno);
            $ficha->rol = getStrRol($ficha->rol);
            $ficha->fecha_fin_aislamiento = $ficha->PcrPruebaMolecular->resultado ? $ficha->created_at->addDays(13)->format('d-m-Y') : null;
            $ficha->PcrPruebaMolecular->resultado = getStrPcrResult($ficha->PcrPruebaMolecular->resultado);
            $ficha->PcrPruebaMolecular->fecha = $ficha->PcrPruebaMolecular->created_at->format('d-m-Y');
        }

        $destinatarios = array('fzegarra@fmi.com', 'juan.sanchez@isos-peru.com', 'luis.ampuero@isos-peru.com', 'pperalta1@fmi.com');

        $copia = array(
            'cristhian.vargas@isos-peru.com', 'samuel.larico@isos-peru.com', 'ramiro.linares@isos-peru.com','alejandra.bustamante@isos-peru.com',
            'aldair.duran@isos-peru.com', 'yadira.alfaro@isos-peru.com', 'responsecenter@isos-peru.com'
        );

        /*$destinatarios = array('samuel.larico@isos-peru.com');

        $copia = array('samuel.larico@isos-peru.com');*/

        Mail::to($destinatarios)
            ->cc($copia)
            ->send(new MailEnvioPcrMina($fichas));

        return 0;
    }
}

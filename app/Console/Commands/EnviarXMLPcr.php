<?php

namespace App\Console\Commands;

use App\Mail\EnvioXMLPcrNegativos;
use App\PruebaSerologica;
use App\Service\PruebaSerologicaService;
use Illuminate\Console\Command;
use App\PacienteIsos;
use App\PcrPruebaMolecular;
use App\FichaPaciente;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class EnviarXMLPcr extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enviar:xml_pcr';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enviar xml CV PCR';

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
        $fechaAnterior = date('Y-m-d', strtotime("-1 days"));

        $hora = date('H');

        $fichas = FichaPaciente::when($hora >= 18, function ($q) use ($fechaActual){
                return $q->where(DB::raw('DATE(created_at)'), $fechaActual);
            }, function ($q) use ($fechaActual, $fechaAnterior) {
                return $q->whereBetween(DB::raw('DATE(created_at)'), [$fechaAnterior, $fechaActual]);
            })->whereHas('PacienteIsos')
            ->whereHas('Estacion', function (Builder $q) {
                $q->whereIn('idsede', [1, 2, 3, 6]);
            })
            ->with('PacienteIsos:idpacientes,numero_documento,tipo_documento')
            ->whereHas('PcrPruebaMolecular', function ($q) {
                $q->whereNotNull('resultado')->where('resultado', '<>', 2);
            })
            ->with('PcrPruebaMolecular','DatosClinicos','AntecedentesEp')
            ->with(["PruebaSerologica" => function ($q) {
                $q->where("invalido", 0)
                    ->whereNotNull("no_reactivo")
                    ->latest();
            }])
            ->latest()
            ->get();

        $total = $fichas->count();

        foreach ($fichas as $ficha) {
            foreach ($ficha->PruebaSerologica as $ps) {
                $service = new PruebaSerologicaService($ps);
                $ps->resultado = $service->cvResult();
                $ps->dias_bloqueo = $service->diasBloqueo();
            }
            $ficha->fecha = $ficha->created_at->format('Ymd');
            $ficha->hora = $ficha->created_at->format('His');
            $ficha->proceso = $ficha->turno === 2 ? 14 : 12;
        }

        if ($total > 0) {
            $output = View::make('xml.xml_pcr')->with('fichas', $fichas)->render();
            $datos = array();
            $datos[0] = date('Ymd_Hi');
            $datos[1] = date('Y-m-d');

            Storage::put('xmlpcr/' . $datos[0] . '.xml', $output);
           /*Mail::to(['samuel.larico@isos-peru.com'])
                ->cc(['samuel.larico@isos-peru.com'])
                ->send(new EnvioXMLPcrNegativos($datos));*/
            Mail::to(['fzegarra@fmi.com','jquispea2@fmi.com','npacheco@fmi.com'])
                ->cc(['cristhian.vargas@isos-peru.com', 'samuel.larico@isos-peru.com','aldair.duran@isos-peru.com'])
                ->send(new EnvioXMLPcrNegativos($datos));
        }
        return 0;
    }
}

<?php

namespace App\Console\Commands;

use App\FichaPaciente;
use App\Mail\EnvioXMLPcrMina;
use App\Mail\EnvioXMLPcrNegativos;
use App\Service\PruebaSerologicaService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class EnviarXMLPcrMina extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enviar:xml_pcr_mina';

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
        //dd($this->argument('sede'));
        $dias = now()->dayOfWeek === 1 ? 3 : 2;
        $fechaActual = now()->format('Y-m-d');
        $fechaAnterior = now()->subDays($dias)->format('Y-m-d');

        $fichas = FichaPaciente::whereBetween(DB::raw('DATE(created_at)'), [$fechaAnterior, $fechaActual])
            ->whereHas('Estacion', function (Builder $q) {
                $q->where('idsede', 4);
            })
            ->whereHas('PcrPruebaMolecular', function (Builder $q) {
                $q->whereNotNull('resultado');
            })
            ->with(["PruebaSerologica" => function ($q) {
                $q->where("invalido", 0)
                    ->whereNotNull("no_reactivo")
                    ->latest();
            }])
            ->latest()
            ->get();

        foreach ($fichas as $ficha) {
            foreach ($ficha->PruebaSerologica as $ps) {
                $service = new PruebaSerologicaService($ps);
                $ps->resultado = $service->cvResult();
                $ps->dias_bloqueo = $service->diasBloqueo();
            }
            $ficha->fecha = $ficha->created_at->format('Ymd');
            $ficha->hora = $ficha->created_at->format('His');
            $ficha->proceso = 13;
        }

        $total = $fichas->count();

        if ($total > 0) {
            $output = View::make('xml.xml_pcr')->with('fichas', $fichas)->render();
            $datos = array();
            $datos[0] = date('Ymd_Hi');
            $datos[1] = date('Y-m-d');

            Storage::put('xmlpcrmina/' . $datos[0] . '.xml', $output);
            /*Mail::to(['samuel.larico@isos-peru.com'])
                 ->cc(['samuel.larico@isos-peru.com'])
                 ->send(new EnvioXMLPcrMina($datos));*/
            Mail::to(['fzegarra@fmi.com','jquispea2@fmi.com','npacheco@fmi.com'])
                ->cc(['cristhian.vargas@isos-peru.com', 'samuel.larico@isos-peru.com','aldair.duran@isos-peru.com'])
                ->send(new EnvioXMLPcrMina($datos));
        }

        return 0;
    }
}

<?php

namespace App\Console\Commands;

use App\Service\PruebaSerologicaService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use App\FichaPaciente;
use App\Mail\EnvioXML;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

class EnviarXML extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enviar:xml';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia XML a Cerro Verde';

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
        $fichas = FichaPaciente::whereDate('created_at', now()->format('Y-m-d'))
            ->doesntHave("CitasMw")
            ->whereHas("PacienteIsos")
            ->whereHas('Estacion', function (Builder $q) {
                $q->whereIn('idsede', [1, 2, 3, 6]);
            })
            ->whereHas("PruebaSerologica", function (Builder $q) {
                $q->where("invalido", 0)->whereNotNull("no_reactivo");
            })
            ->with("PacienteIsos:idpacientes,numero_documento,tipo_documento")
            ->with('DatosClinicos','AntecedentesEp')
            ->with(["PruebaSerologica" => function ($q) {
                $q->where("invalido", 0)
                    ->whereNotNull("no_reactivo")
                    ->latest();
            }])
            ->latest()
            ->get();

        foreach ($fichas as $ficha) {
            foreach ($ficha->PruebaSerologica as $ps) {
                try {
                    $service = new PruebaSerologicaService($ps);
                    $ps->resultado = $service->cvResult();
                    $ps->dias_bloqueo = $service->diasBloqueo();
                } catch (\Exception $ex) {
                    //dd($ps);
                }
            }
            $ficha->fecha = $ficha->created_at->format('Ymd');
            $ficha->hora = $ficha->created_at->format('His');
            $ficha->proceso = $ficha->turno === 2 ? 14 : 12;
        }
        if($fichas->count() > 0) {
            $output = View::make('xml.xml_prs')->with('fichas', $fichas)->render();
            $datos = array();
            $datos[0] = date('Ymd_Hi');
            $datos[1] = date('Y-m-d');

            Storage::put('xml/'.$datos[0].'.xml', $output);
            Mail::to(['fzegarra@fmi.com','jquispea2@fmi.com','npacheco@fmi.com'])
                ->cc(['cristhian.vargas@isos-peru.com','samuel.larico@isos-peru.com','aldair.duran@isos-peru.com'])
                ->send(new EnvioXML($datos));

            /*Mail::to(['samuel.larico@isos-peru.com'])
                ->cc(['samuel.larico@isos-peru.com'])
                ->send(new EnvioXML($datos));*/
        }
        return 0;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\ConsultaWhatsappAg;
use App\ConsultaWhatsAppPcr;
use App\ConsultaWhatsAppPrs;
use App\EnvioWpAg;
use App\MensajeWhatsApp;
use App\FichaPaciente;
use App\Service\PruebaAntigenaService;
use App\Service\PruebaMolecularService;
use App\Service\PruebaSerologicaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use App\EnvioWP;
use App\PruebaSerologica;
use App\EnvioWpPcr;
use App\PcrPruebaMolecular;

class ExtraerGente extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'extraer:gente';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Saca a la gente sin resultado y mas de 20 minutos';

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

        $fichas = FichaPaciente::where(DB::raw('DATE(created_at)'), $fechaActual)
            ->where('created_at', '<', now()->subMinutes(20)->toDateTimeString())
            ->where('conforme_prs', '!=', 1)
            ->where('enviar_mensaje', 1)
            ->whereHas('PacienteIsos')
            ->whereHas('Estacion', function (Builder $q) {
                $q->whereIn('idsede', [7, 3]);
            })

            ->whereHas('PruebaSerologica', function ($q) {
                $q->whereNotNull('p1_react1gm')
                    ->whereNotNull('p1_reactigg')
                    ->whereNotNull('p1_reactigm_igg')
                    ->whereNotNull('invalido');
            })
            ->with(["PruebaSerologica" => function ($q) {
                $q->where("invalido", 0)
                    //->whereNotNull("no_reactivo")
                    ->latest();
            }])
            //->where('id_paciente', '5624')
            ->with('PacienteIsos:idpacientes,numero_documento,tipo_documento,celular')
            // ->latest()
            //->limit(100)
            ->get()
            ->count()
            ;

        //dd($fichas);
        //Foo::whereBetween('created_at', [now()->subMinutes(3), now()])->get();
       

        echo  $fichas ;
       
    }
}

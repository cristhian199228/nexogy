<?php

namespace App\Console\Commands;

use App\CitasAutomaticasMw;
use App\CitasMw;
use App\ExcelNicole;
use App\FichaPaciente;
use App\PacienteIsos;
use App\PcrPruebaMolecular;
use App\PruebaSerologica;
use App\Service\MediwebService;
use App\Service\PruebaMolecularService;
use App\Service\PruebaSerologicaService;
use Illuminate\Console\Command;
use App\DatosClinicos;
use App\AntecedentesEp;
use App\Temperatura;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use DateTime;
use App\Paciente;
use App\Empresa;
use Illuminate\Support\Facades\DB;


class CitaAutomaticaMw extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cita_auto:mw';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'comando de  creacion de citas automaticas en mediweb de personal de cv pcr positivo';

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
        //creacion nde citas solo para personal de cv positivo
        $fechaActual = date('Y-m-d');
        $fechaAnterior = date('Y-m-d',strtotime("-1 days"));

        $fichas = FichaPaciente::whereBetween(DB::raw('DATE(created_at)'), [$fechaAnterior, $fechaActual])
            ->whereHas('Estacion', function (Builder $q) {
                $q->whereIn('idsede', [1, 2, 3, 4, 6]);
            })
            ->doesntHave("CitasMw")
            ->whereHas('PacienteIsos', function ($q) {
                $q->where('idempresa', 7);
            })
            ->whereHas('PcrPruebaMolecular', function ($q) {
                $q->where('resultado', 1);
            })
            ->with(["PruebaSerologica" => function ($q) {
                $q->where("invalido", 0)
                    ->whereNotNull("no_reactivo")
                    ->latest();
            }])
            ->latest()
            ->get();

        foreach ($fichas as $ficha) {

            $prsResult = count($ficha->PruebaSerologica) > 0 ?
                (new PruebaSerologicaService($ficha->PruebaSerologica[0]))->cvResult() : '';
            $pcrResult = (new PruebaMolecularService($ficha->PcrPruebaMolecular))->strResult();
            $service = new MediwebService($ficha, $prsResult, $pcrResult);
            $result = $service->sendRequest();

            if($result) {
                $cita = new CitasMw();
                $cita->idficha_paciente = $ficha->idficha_paciente;
                $cita->tipo = 2;
                $cita->cadena_envio = json_encode($service->getParams());
                $cita->respuesta_ws = json_encode($result);
                $cita->save();
            }
        }

        return 0;
    }
}

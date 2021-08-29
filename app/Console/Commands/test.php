<?php

namespace App\Console\Commands;

use App\FichaPaciente;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:test';

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
        $fechaActual = date('Y-m-d');
        $fechaAnterior = date('Y-m-d', strtotime("-1 days"));

        $hora = date('H');

        $fichas = FichaPaciente::when($hora >= 18, function ($q) use ($fechaActual){
                return $q->where(DB::raw('DATE(created_at)'), $fechaActual);
            }, function ($q) use ($fechaActual, $fechaAnterior) {
                return $q->whereBetween(DB::raw('DATE(created_at)'), [$fechaAnterior, $fechaActual]);
            })
            ->whereHas('PacienteIsos')
            ->whereHas('PcrPruebaMolecular', function ($q) {
                $q->whereHas("PcrEnvioMunoz")
                    ->whereNull('resultado');
            })
            ->with("PacienteIsos")
            ->with('PcrPruebaMolecular.PcrEnvioMunoz')
            ->get();
        return 0;
    }
}

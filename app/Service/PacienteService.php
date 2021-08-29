<?php

namespace App\Service;

use App\FichaPaciente;
use App\PacienteIsos;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PacienteService {

    /**
     * @var PacienteIsos
     */
    private $paciente;

    public function __construct(PacienteIsos $paciente)
    {
        $this->paciente = $paciente;
    }

    public function esPositivoPcrTerceraVez() : bool  {

        $pruebas = FichaPaciente::where('id_paciente', $this->paciente->idpacientes)
            ->whereBetween(DB::raw('date(created_at)'), [
                now()->subMonths(3)->format('Y-m-d'),
                now()->format('Y-m-d')
            ])
            ->whereHas('PcrPruebaMolecular', function (Builder $q) {
                $q->whereNotNull('resultado');
            })
            ->latest()->take(3)->get();

        $nroVecesPositivo = $pruebas->filter(function ($pcr) {
            return $pcr->PcrPruebaMolecular->resultado === 1;
        })->count();

        if ($nroVecesPositivo > 2) return true;

        return false;
    }

}
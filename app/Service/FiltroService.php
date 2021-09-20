<?php

namespace App\Service;

use Illuminate\Database\Eloquent\Builder;

class FiltroService {

    private $collection;

    public function __construct($collection)
    {
        $this->collection = $collection;
    }

    public function desde($buscar) : void {
        $this->collection->where('Extension', 'LIKE', '%' . $buscar . '%');
        
    }

    public function para($buscar) : void {
        $this->collection->where('PhoneNumber', 'LIKE', '%' . $buscar . '%');
        
    }
    public function direccion($buscar) : void {
        $this->collection->where('Direction', '=', $buscar );
        
    }
    public function empresa($buscar) : void {
        $this->collection->whereHas('PacienteIsos.Empresa', function (Builder $q) use ($buscar) {
            $q->where('descripcion', 'LIKE', '%' . $buscar . '%')
                ->orWhere('nombrecomercial', 'LIKE', '%' . $buscar . '%')
                ->orWhere('ruc', 'LIKE', '%' . $buscar . '%');
        });
    }

    public function pruebaSerologica($ps) : void {
        $this->collection->whereHas('PruebaSerologica', function (Builder $q) use ($ps) {
            switch ($ps) {
                case 2: return $q->where('p1_reactigg', 1)->where('p1_positivo_recuperado', 1);
                case 3: return $q->where('p1_reactigg', 1)->where('p1_positivo_vacunado', 1);
                case 4: return $q->where('p1_reactigg', 1)->where('p1_positivo_recuperado', 0)
                    ->where('p1_positivo_vacunado', 0);
                case 5: return $q->where('p1_react1gm', 1)->where('p1_positivo_persistente', 0);
                case 6: return $q->where('p1_reactigm_igg', 1)->where('p1_positivo_persistente', 0);
                case 7: return $q->where('p1_react1gm', 1)->where('p1_positivo_persistente', 1);
                case 8: return $q->where('p1_reactigm_igg', 1)->where('p1_positivo_persistente', 1);
                default: return $q->where('no_reactivo', 1);
            }
        });
    }

    public function pruebaMolecular($pcr) : void {
        $this->collection->whereHas('PcrPruebaMolecular', function (Builder $q) use ($pcr) {
            switch ($pcr) {
                case 0: return $q->where('resultado', 0);
                case 1: return $q->where('resultado', 1);
                case 2: return $q->where('resultado', 2);
                default: return $q->whereNull('resultado');
            }
        });
    }

    public function sede($sede) : void {
        $this->collection->whereHas('Estacion', function ($q) use ($sede) {
            $q->where('estaciones.idsede', $sede);
        });
    }
}
<?php

namespace App\Service;

use App\Instancia;
use Illuminate\Support\Facades\DB;

class InstanciaService {

    private $instancias;

    public function __construct()
    {
        $this->instancias = Instancia::all();
    }

    private function instanciasDisponibles() {
        return $this->instancias->filter(function ($instancia) {
            return $instancia->estado === 1;
        });
    }

    private function nroInstanciasDisponibles() {
        return $this->instanciasDisponibles()->count();
    }

    private function mensajesPorMinuto() : int {
        $envios_prs = DB::table('envio_whatsapp_pcr')
            ->where(DB::raw("date_format(created_at, '%Y-%m-%d %H:%i')"), date('Y-m-d H:i'))
            ->count();

        $envios_pcr = DB::table('envio_whatsapp_prs')
            ->where(DB::raw("date_format(created_at, '%Y-%m-%d %H:%i')"), date('Y-m-d H:i'))
            ->count();

        $envios_ag = DB::table('envio_whatsapp_ag')
            ->where(DB::raw("date_format(created_at, '%Y-%m-%d %H:%i')"), date('Y-m-d H:i'))
            ->count();

        $envios_evidencias = DB::table('envio_whatsapp_evidencias')
            ->where(DB::raw("date_format(created_at, '%Y-%m-%d %H:%i')"), date('Y-m-d H:i'))
            ->count();

        $envios_confirmacion = DB::table('envio_whatsapp_confirmacion')
            ->where(DB::raw("date_format(created_at, '%Y-%m-%d %H:%i')"), date('Y-m-d H:i'))
            ->count();

        return $envios_pcr + $envios_prs + $envios_ag + $envios_evidencias + $envios_confirmacion;
        //return 90;
    }

    public function instanciaDisponible() {
       /* if ($this->mensajesPorMinuto() > 30) {
            throw new \Exception('Numero de mensajes enviado excedido');
        }*/
        /*if ($this->nroInstanciasDisponibles() === 3) {
            if ($this->mensajesPorMinuto() > 20) {
                return $this->instanciasDisponibles()->firstWhere('id', 3);
            }
            if ($this->mensajesPorMinuto() > 10) {
                return $this->instanciasDisponibles()->firstWhere('id', 2);
            }
            return $this->instanciasDisponibles()->firstWhere('id', 1);
        }
        if ($this->nroInstanciasDisponibles() === 2) {
            if ($this->mensajesPorMinuto() > 20) {
                return $this->instanciasDisponibles()->sortByDesc('id')->first();
            }
            return $this->instanciasDisponibles()->sortBy('id')->first();
        }

        return $this->instanciasDisponibles()->first();*/
        return $this->instanciasDisponibles()->random();
    }

}
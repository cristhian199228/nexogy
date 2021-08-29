<?php

namespace App\Service;

use App\PacienteIsos;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MinsaService {

    private const SEXOS = ['', 'M', 'F'];
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getFechaNacimiento(): ?string {
        return isset($this->data['fecha_nacimiento']) &&  $this->data['fecha_nacimiento'] ?
            Carbon::parse($this->data['fecha_nacimiento'])->format('Y-m-d') :
            '1990-01-01';
    }

    public function getEstado(): int {
        return isset($this->data['fecha_nacimiento']) &&  $this->data['fecha_nacimiento'] ? 1 : 2;
    }

    private static function quitarEspacios($str) {
        return preg_replace('!\s+!', ' ', trim($str));
    }

    public function getSexo() : ?string {
        return isset($this->data['sexo']) && $this->data['sexo'] ? self::SEXOS[$this->data['sexo']] : 'M';
    }

    public function getCorreo() : ?string {
        return isset($this->data['correo']) && $this->data['correo'] ? Str::lower($this->data['correo']) : null;
    }

    public function getValue($key) : ?string {
        $datosPersonales = ['nombres', 'apellido_paterno', 'apellido_materno'];
        if (isset($this->data[$key]) && $this->data[$key]) {
            if (isset($datosPersonales[$key])) return Str::upper(self::quitarEspacios($this->data[$key]));
            return $this->data[$key];
        }
        return null;
    }

    public function getFoto() : ?string {
        if (isset($this->data['foto']) && $this->data['foto']) {
            $foto_base64 = $this->data['foto'];
            $imageName = Str::random(10) . '.' . 'jpg';
            $foto = md5($imageName . time()) . '.jpg';
            Storage::disk('ftp')->put('/FP/' . $foto,  base64_decode($foto_base64));

            return $foto;
        }
        return null;
    }
}
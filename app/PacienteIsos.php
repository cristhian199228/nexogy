<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use AjCastro\Searchable\Searchable;


class PacienteIsos extends Model
{
    //
    use Searchable;

    protected $table = "pacientes";
    protected $primaryKey = "idpacientes";
    protected $fillable = [
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'fecha_nacimiento',
        'tipo_documento',
        'numero_documento',
        'sexo',
        'residencia_pais',
        'residencia_departamento',
        'residencia_provincia',
        'residencia_distrito',
        'direccion',
        'celular',
        'correo',
        'latitud',
        'longitud',
        'foto',
        'estado',
        'idempresa',
        'nro_registro',
        'puesto'
    ];

    protected $searchable = [
        // Searchable columns of the model.
        // If this is not defined it will default to all table columns.
        'columns' => [
            'pacientes.numero_documento',
            'pacientes.nombres',
            'pacientes.apellido_paterno',
            'pacientes.apellido_materno',
            'nombre_completo' => 'CONCAT(pacientes.nombres, " ", pacientes.apellido_paterno, " ", pacientes.apellido_materno )'
        ],
    ];

    protected $appends = ['full_name'];

    public function getFullNameAttribute() {
        return "{$this->nombres} {$this->apellido_paterno} {$this->apellido_materno}";
    }

    public function Empresa() {
        return $this->belongsTo('App\Empresa','idempresa','idempresa');
    }
    public function ExcelNicole(){
        return $this->hasOne('App\ExcelNicole','numero_documento','numero_documento');
    }

    public function FichaPaciente() {
        return $this->hasMany('App\FichaPaciente','id_paciente','idpacientes');
    }

    public function DepartamentoUbigeo() {
        return $this->belongsTo("App\DepartamentoUbigeo","residencia_departamento");
    }

    public function ProvinciaUbigeo() {
        return $this->belongsTo("App\ProvinciaUbigeo","residencia_provincia");
    }

    public function DistritoUbigeo() {
        return $this->belongsTo("App\DistritoUbigeo","residencia_distrito");
    }

    public function evidencia () {
        return $this->hasMany('App\EvidenciaRC', 'id_paciente');
    }
    
    public function fichaTemporal() {
        return $this->hasMany('App\FichaTemporal', 'id_paciente', 'idpacientes');
    }

    public function programacionCv() {
        return $this->hasMany('App\ProgramacionDiariaCv', 'numero_documento', 'numero_documento');
    }
}

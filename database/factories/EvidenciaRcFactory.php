<?php

/** @var Factory $factory */


use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\DB;

$factory->define(App\EvidenciaRC::class, function (Faker $faker) {
    return [
        //
        'id_paciente' => DB::table('pacientes')->inRandomOrder()->first()->idpacientes,
        'id_estacion' => DB::table('estaciones')->inRandomOrder()->first()->idestaciones,
        'usuario' => $faker->email
    ];
});

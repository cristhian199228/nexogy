<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\FichaPaciente;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\DB;

$factory->define(FichaPaciente::class, function (Faker $faker) {
    return [
        //
        'id_paciente' => 10657,
        'id_estacion' => DB::table('estaciones')->where('idsede', 3)->inRandomOrder()->first()->idestaciones,
        'id_empresa' => 12,
        'hash' => now()->format('Ymd') . $faker->randomNumber(8, true),
        'turno' => $faker->randomElement([1, 2]),
        'rol' => $faker->randomElement([1, 2]),
        'enviar_mensaje' => 1,
        'usuario' => $faker->email
    ];
});

/*$factory->state(FichaPaciente::class, 'cerro_verde', [
    'id_paciente' => DB::table('pacientes')->where('idempresa', 7)->inRandomOrder()->first()->idpacientes,
]);*/

$factory->state(FichaPaciente::class, 'smcv', [
    'id_estacion' => DB::table('estaciones')->where('idsede', 4)->inRandomOrder()->first()->idestaciones,
]);

$factory->state(FichaPaciente::class, 'acuartelado', [
    'rol' => 1
]);

$factory->state(FichaPaciente::class, 'itinerante', [
    'rol' => 2
]);

$factory->state(FichaPaciente::class, 'subida', [
    'turno' => 1
]);

$factory->state(FichaPaciente::class, 'bajada', [
    'turno' => 2
]);

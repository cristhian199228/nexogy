<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\PruebaSerologica;
use Faker\Generator as Faker;

$factory->define(PruebaSerologica::class, function (Faker $faker) {
    return [
        //
        'ps_llamada_113' => $faker->boolean,
        'ps_contactocasocon' => $faker->boolean,
        'ps_contactocasosos' => $faker->boolean,
        'ps_personaext' => $faker->boolean,
        'ps_personalsalud' => $faker->boolean,
        'ps_otro' => $faker->randomElement(['SMCV' , 'G4S', 'ADECCO']),
        'p1_react1gm' => $faker->boolean,
        'p1_reactigg' => $faker->boolean,
        'p1_reactigm_igg' => $faker->boolean,
        'invalido' => $faker->boolean,
        'ccs' => $faker->randomElement([0, 1]),
        'condicion_riesgo' => $faker->boolean,
        'condicion_riesgo_detalle' => $faker->word,
        'ps_de_eess' => $faker->boolean,
        'no_reactivo' => $faker->boolean,
        'hora_inicio' => now(),
        'hora_fin' => now()->addMinutes(15),
        'p1_positivo_recuperado' => $faker->boolean,
        'p1_marca' => $faker->randomElement([1, 2, 3, 4]),
        'usuario' => $faker->email,
        'codigo_ps'=> $faker->word,
        'positivo_anterior' => $faker->boolean,
        'fecha_positivo_anterior' => $faker->date(),
        'lugar_positivo_anterior' => $faker->word,
        'p1_positivo_persistente' => $faker->boolean,
        'p1_positivo_vacunado' => $faker->boolean,
    ];
});

$factory->state(PruebaSerologica::class, 'no_reactivo', [
    'no_reactivo' => 1,
    'p1_react1gm' => 0,
    'p1_reactigg' => 0,
    'p1_reactigm_igg' => 0,
    'invalido' => 0,
    'p1_positivo_recuperado' => 0,
    'p1_positivo_vacunado' => 0,
    'p1_positivo_persistente' => 0
]);

$factory->state(PruebaSerologica::class, 'igg_recuperado', [
    'no_reactivo' => 0,
    'p1_react1gm' => 0,
    'p1_reactigg' => 1,
    'p1_reactigm_igg' => 0,
    'invalido' => 0,
    'p1_positivo_recuperado' => 1,
    'p1_positivo_vacunado' => 0,
    'p1_positivo_persistente' => 0
]);
$factory->state(PruebaSerologica::class, 'igg_vacunado', [
    'no_reactivo' => 0,
    'p1_react1gm' => 0,
    'p1_reactigg' => 1,
    'p1_reactigm_igg' => 0,
    'invalido' => 0,
    'p1_positivo_recuperado' => 0,
    'p1_positivo_vacunado' => 1,
    'p1_positivo_persistente' => 0
]);
$factory->state(PruebaSerologica::class, 'igg', [
    'no_reactivo' => 0,
    'p1_react1gm' => 0,
    'p1_reactigg' => 1,
    'p1_reactigm_igg' => 0,
    'invalido' => 0,
    'p1_positivo_recuperado' => 0,
    'p1_positivo_vacunado' => 0,
    'p1_positivo_persistente' => 0
]);
$factory->state(PruebaSerologica::class, 'igm', [
    'no_reactivo' => 0,
    'p1_react1gm' => 1,
    'p1_reactigg' => 0,
    'p1_reactigm_igg' => 0,
    'invalido' => 0,
    'p1_positivo_recuperado' => 0,
    'p1_positivo_vacunado' => 0,
    'p1_positivo_persistente' => 0
]);
$factory->state(PruebaSerologica::class, 'igm_igg', [
    'no_reactivo' => 0,
    'p1_react1gm' => 0,
    'p1_reactigg' => 0,
    'p1_reactigm_igg' => 1,
    'invalido' => 0,
    'p1_positivo_recuperado' => 0,
    'p1_positivo_vacunado' => 0,
    'p1_positivo_persistente' => 0
]);

$factory->state(PruebaSerologica::class, 'igm_persistente', [
    'no_reactivo' => 0,
    'p1_react1gm' => 1,
    'p1_reactigg' => 0,
    'p1_reactigm_igg' => 0,
    'invalido' => 0,
    'p1_positivo_recuperado' => 0,
    'p1_positivo_vacunado' => 0,
    'p1_positivo_persistente' => 1
]);

$factory->state(PruebaSerologica::class, 'igm_igg_persistente', [
    'no_reactivo' => 0,
    'p1_react1gm' => 0,
    'p1_reactigg' => 0,
    'p1_reactigm_igg' => 1,
    'invalido' => 0,
    'p1_positivo_recuperado' => 0,
    'p1_positivo_vacunado' => 0,
    'p1_positivo_persistente' => 1
]);

$factory->state(PruebaSerologica::class, 'invalido', [
    'no_reactivo' => 0,
    'p1_react1gm' => 0,
    'p1_reactigg' => 0,
    'p1_reactigm_igg' => 0,
    'invalido' => 1,
    'p1_positivo_recuperado' => 0,
    'p1_positivo_vacunado' => 0,
    'p1_positivo_persistente' => 0
]);

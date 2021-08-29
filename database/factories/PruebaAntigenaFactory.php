<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\PruebaAntigena;
use Faker\Generator as Faker;

$factory->define(PruebaAntigena::class, function (Faker $faker) {
    return [
        'llamada_113' => $faker->boolean,
        'prueba_eess' => $faker->boolean,
        'personal_salud' => $faker->boolean,
        'contacto_caso_confirmado' => $faker->boolean,
        'contacto_caso_sospechoso' => $faker->boolean,
        'persona_extranjero' => $faker->boolean,
        'persona_conglomerados' => $faker->boolean,
        'otros' => $faker->word,
        'clasificacion_clinica_severidad' => $faker->randomElement([1, 2, 3, 4]),
        'condicion_riesgo' => $faker->randomNumber(1, true) + 2,
        'condicion_riesgo_otro' => null,
        'marca_prueba' => 1,
        'tipo_muestra' => $faker->randomElement([1, 2, 3, 4]),
        'tipo_lectura'=> $faker->randomElement([1, 2]),
        'observaciones' => $faker->word,
        'resultado' => $faker->randomElement([0, 1, 2, 3]),
        'usuario' => $faker->email,
        'started_at' => now(),
        'finished_at' => now()->addMinutes(20),
    ];
});

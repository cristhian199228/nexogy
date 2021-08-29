<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\DatosClinicos;
use Faker\Generator as Faker;

$factory->define(DatosClinicos::class, function (Faker $faker) {
    return [
        'tos' => $faker->boolean,
        'dolor_garganta' => $faker->boolean,
        'dificultad_respiratoria' => $faker->boolean,
        'fiebre' => $faker->boolean,
        'malestar_general' => $faker->boolean,
        'diarrea' => $faker->boolean,
        'anosmia_ausegia' => $faker->boolean,
        'otros' => $faker->word,
        'toma_medicamento' => $faker->word,
        'nauseas_vomitos' => $faker->boolean,
        'congestion_nasal' => $faker->boolean,
        'cefalea' => $faker->boolean,
        'irritabilidad_confusion' => $faker->boolean,
        'falta_aliento' => $faker->boolean,
        'usuario' => $faker->email,
        'fecha_inicio_sintomas' => $faker->date(),
        'dolor_muscular' => $faker->boolean,
        'dolor_abdominal' => $faker->boolean,
        'dolor_articulaciones' => $faker->boolean,
        'dolor_pecho' => $faker->boolean,
        'post_vacunado' => $faker->boolean,
    ];
});

$factory->state(DatosClinicos::class, 'post_vacunado', [
    'post_vacunado' => true
]);

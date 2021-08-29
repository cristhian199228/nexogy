<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\PcrPruebaMolecular;
use Faker\Generator as Faker;

$factory->define(PcrPruebaMolecular::class, function (Faker $faker) {
    return [
        //
        'hash' => $faker->randomNumber(8, true) . now()->format('Ymd'),
        'hora_fin' => now()->addHours(10),
        'resultado' => $faker->randomElement([null, 0, 1, 2]),
        'usuario' => $faker->email,
        'precio' => $faker->randomNumber(3),
        'tipo' => $faker->randomElement([1, 2]),
        'detalle' => $faker->text,
    ];
});

$factory->state(PcrPruebaMolecular::class, 'negativo', [
    'resultado' => 0
]);

$factory->state(PcrPruebaMolecular::class, 'positivo', [
    'resultado' => 1
]);

$factory->state(PcrPruebaMolecular::class, 'sin_resultado', [
    'resultado' => null
]);

<?php

use Illuminate\Database\Seeder;

class FichaPacienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        factory(App\FichaPaciente::class, 3)->create()
            ->each(function ($ficha) {
               /* $ficha->PruebaSerologica()
                    ->save(factory(App\PruebaSerologica::class)
                        ->state('igm')
                        ->create()
                    );*/
                $ficha->PcrPruebaMolecular()
                    ->save(factory(App\PcrPruebaMolecular::class)
                        ->state('positivo')
                        ->create()
                    );
                /*$ficha->DatosClinicos()
                    ->save(factory(App\DatosClinicos::class)
                        ->state('post_vacunado')
                        ->create()
                    );*/
                /*$ficha->pruebaAntigena()
                    ->save(factory(App\PruebaAntigena::class)
                        ->create()
                    );*/
            });
    }
}

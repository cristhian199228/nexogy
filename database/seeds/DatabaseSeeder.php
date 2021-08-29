<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        //$this->call(EvidenciasSeed::class);
        $this->call(FichaPacienteSeeder::class);
        //$this->call(EvidenciaRcSeeder::class);
    }
}

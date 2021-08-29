<?php

use Illuminate\Database\Seeder;

class EvidenciaRcSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        factory(App\EvidenciaRC::class, 300)->create();
    }
}

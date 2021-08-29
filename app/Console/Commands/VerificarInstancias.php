<?php

namespace App\Console\Commands;

use App\Instancia;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class VerificarInstancias extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verificar:instancias';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $instancias = Instancia::all();

        foreach ($instancias as $instancia) {
            $response = Http::get($instancia->url . 'status', [
                'token' => $instancia->token
            ]);
            $estado = 0;
            if ($response->successful() &&
                isset($response->json()['accountStatus']) &&
                $response->json()['accountStatus'] === 'authenticated')
            {
                $estado = 1;
            }
            $instancia->update(['estado' => $estado]);
        }
        return 0;
    }
}

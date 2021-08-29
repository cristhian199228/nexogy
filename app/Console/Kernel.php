<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('enviar:xml')->dailyAt('08:58');
        $schedule->command('enviar:xml')->dailyAt('11:58');
        $schedule->command('enviar:xml')->dailyAt('14:58');
        $schedule->command('enviar:xml')->dailyAt('23:55');

        $schedule->command('enviar:xml_pcr')->dailyAt('18:30');
        $schedule->command('enviar:xml_pcr')->dailyAt('21:30');
        $schedule->command('enviar:xml_pcr')->dailyAt('00:30');
        $schedule->command('enviar:xml_pcr')->dailyAt('02:30');
        $schedule->command('enviar:xml_pcr')->dailyAt('04:00');

        $schedule->command('enviar:excel_pcr_cv')->dailyAt('18:30');
        $schedule->command('enviar:excel_pcr_cv')->dailyAt('21:30');
        $schedule->command('enviar:excel_pcr_cv')->dailyAt('00:30');
        $schedule->command('enviar:excel_pcr_cv')->dailyAt('02:30');
        $schedule->command('enviar:excel_pcr_cv')->dailyAt('04:00');

        $schedule->command('enviar:excel_pcr_con')->dailyAt('18:30');
        $schedule->command('enviar:excel_pcr_con')->dailyAt('21:30');
        $schedule->command('enviar:excel_pcr_con')->dailyAt('00:30');
        $schedule->command('enviar:excel_pcr_con')->dailyAt('02:30');
        $schedule->command('enviar:excel_pcr_con')->dailyAt('04:00');

        $schedule->command('enviar:excel_pcr_mina')->dailyAt('08:00');
        $schedule->command('enviar:excel_pcr_mina')->mondays()->at('18:30');
        $schedule->command('enviar:excel_pcr_mina')->mondays()->at('21:30');

        $schedule->command('enviar:xml_pcr_mina')->dailyAt('08:00');
        $schedule->command('enviar:xml_pcr_mina')->mondays()->at('18:30');
        $schedule->command('enviar:xml_pcr_mina')->mondays()->at('21:30');
        //$schedule->command('enviar:excel_pcr_mina')->dailyAt('21:30');

        $schedule->command('consumir:ws_munoz_pcr')->everyTenMinutes();
        //$schedule->command('verificar:instancias')->everyTenMinutes();
        //$schedule->command('consumir:ws_munoz_pcr_temp')->everyTenMinutes();
        $schedule->command('enviar:wp_pcr')->everyFiveMinutes();
        $schedule->command('cita_auto:mw')->everyTenMinutes();
        //$schedule->command('verificar:pcrcv_fin')->everyTenMinutes();
        //$schedule->command('verificar:pcrcon_fin')->everyTenMinutes();

        $schedule->command('backup:clean')->dailyAt('01:30');
        $schedule->command('backup:run')->dailyAt('01:35');
        //$schedule->command('backup:run')->dailyAt('13:56');
        $schedule->command('revisar:wp_prs')->everyMinute();
        $schedule->command('envio:wpaut')->everyMinute();
        //$schedule->command('revisar:wp_prs')->everyThreeMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}

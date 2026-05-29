<?php

namespace App\Console;

use App\Jobs\AlertasRotacionInventarioJob;
use App\Jobs\EscalarNotificacionesJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Verificar notificaciones sin leer y escalar según configuración
        $schedule->job(new EscalarNotificacionesJob)->hourly();

        // Evaluar alertas inteligentes de rotación e inventario (diario a las 6:00 AM)
        $schedule->job(new AlertasRotacionInventarioJob)->dailyAt('06:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

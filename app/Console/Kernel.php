<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('expired_booking:cron')->everyMinute();
        $schedule->command('notif_bulanan:cron')->daily();
        $schedule->command('due-date:cron')->daily();
        $schedule->command('iuran:cron')->daily();
        $schedule->command('tunggakan:cron')->daily();
        $schedule->command('zero:cron')->daily();
        $schedule->command('resolve:cron')->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}

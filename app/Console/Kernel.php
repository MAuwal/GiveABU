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
        // Clean up expired pending donations daily at 2:00 AM
        $schedule->command('donations:cleanup-pending')
            ->dailyAt('02:00')
            ->name('cleanup-pending-donations')
            ->withoutOverlapping();

        // Optional: Run more frequently in development
        if ($this->app->environment('local')) {
            $schedule->command('donations:cleanup-pending')
                ->everyFiveMinutes()
                ->name('cleanup-pending-donations-dev');
        }

        // Run the verification command every minute (fallback if daemon isn't running)
        $schedule->command('donations:verify-pending')
            ->everyMinute()
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
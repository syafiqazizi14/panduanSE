<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\GenerateUmkmSeed::class,
        \App\Console\Commands\SyncVerificationToWorkbook::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // Daily seed refresh at 02:00 AM
        $schedule->command('umkm:generate-seed')
            ->dailyAt('02:00')
            ->onSuccess(function () {
                // Optional: Send success notification
                // Log::info('UMKM seed refreshed successfully');
            })
            ->onFailure(function () {
                // Optional: Send failure alert
                // Log::error('UMKM seed refresh failed');
            });

        $schedule->command('umkm:sync-verification-sheet --limit=500')
            ->everyTenMinutes();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

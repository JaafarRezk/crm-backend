<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\UpdateClientStatusCommand;
use App\Jobs\SendDueFollowUpNotifications;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        UpdateClientStatusCommand::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('crm:update-client-statuses')->dailyAt('01:00')->withoutOverlapping();
        $schedule->job(new SendDueFollowUpNotifications())->dailyAt('08:00')->withoutOverlapping();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}



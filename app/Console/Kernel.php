<?php
namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Register the application's commands.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\PharmacyExpiryReminder\PharmacyExpiryReminderCommand::class,
        \App\Console\Commands\CheckInactiveUsers::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('pharmacy:expiry-reminder')->daily();
        $schedule->command('check:inactive-users')->daily();
        $schedule->command('wcbp:nonconformance-highrisk')->daily();
        $schedule->command('paf:request-reminder')->daily();
        $schedule->command('paf:daily-alert')->dailyAt('18:00');
        $schedule->command('app:check-paf-action-required')->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}

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
        // $schedule->command('inspire')->hourly();

        $schedule->command('app:prepaid-look-up')
             ->withoutOverlapping()
             ->everyMinute();

        $schedule->command('app:postpaid-look-up')
                ->withoutOverlapping()
                ->everyTwoMinutes();

        $schedule->command('app:payment-look-up')
                ->withoutOverlapping()
                ->everyTwoMinutes();

        $schedule->command('app:clean-log')->dailyAt('02:00');

        $schedule->command('monitor:sql-connections')
                ->withoutOverlapping()
                ->everyTenMinutes();

        $schedule->command('app:fcmbflutter-verify-transactions')
                ->withoutOverlapping()
                ->everyFiveMinutes();

        #$schedule->command('app:prepaid-look-up')->everyMinute();
        #$schedule->command('app:postpaid-look-up')->everyTwoMinutes();
        #$schedule->command('app:payment-look-up')->everyTwoMinutes();
       //$schedule->command('app:verifyfcmb-transaction')->everyTwoMinutes();  // app:fcmbflutter-verify-transactions
        #$schedule->command('app:clean-log')->dailyAt('02:00');
      // $schedule->command('app:failed-transactions')->everyFiveMinutes();
      // $schedule->command('app:postpaid-error-fix')->everyFiveMinutes();
      // $schedule->command('app:prepaid-error-fix')->everyFiveMinutes();
       $schedule->command('app:weekly-payment-checkup')->weekly()->mondays()->at(rand(0, 23) . ':' . rand(0, 59));
      // $schedule->command('telescope:prune')->daily();  app:prepaid-error-fix
        
       //Enable task scheduler logging
       $schedule->exec('echo "Task Scheduler Ran: $(date)" >> /var/www/html/IBEDCPAY/storage/logs/scheduler.log')->everyMinute();

      #$schedule->command('monitor:sql-connections')->everyTenMinutes();
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

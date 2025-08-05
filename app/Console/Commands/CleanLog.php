<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-log';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the Laravel log file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $logPath = storage_path('logs/laravel.log');
        $logPath2 = storage_path('logs/paymentlookup.log');
        $logPath3 = storage_path('logs/prepaid.log');
        $logPath4 = storage_path('logs/postpaid.log');
        $logPath5 = storage_path('logs/worker.log');
        $logPath6 = storage_path('logs/failedtransaction.log');
        $logPath7 = storage_path('logs/fcmbpayment.log');
        $logPath8 = storage_path('logs/nopayload.log');
        $logPath9 = storage_path('logs/postpaiderrorfix.log');
        $logPath10 = storage_path('logs/walletbalance.log');
        $logPath11 = storage_path('logs/walletintegration.log');
        $logPath12 = storage_path('logs/weeklyprepaidfix.log');
        if (File::exists($logPath)) {
            // Clear the log file by setting its contents to an empty string
            File::put($logPath, '');
            $this->info('Log file cleared successfully.');

             if(File::exists($logPath2)) {
                File::put($logPath2, '');
                File::put($logPath3, '');
                File::put($logPath4, '');
                File::put($logPath5, '');
                File::put($logPath6, '');
                File::put($logPath7, '');
                File::put($logPath8, '');
                File::put($logPath9, '');
                File::put($logPath10, '');
                File::put($logPath11, '');
                File::put($logPath12, '');
             }
        } else {
            $this->info('Log file does not exist.');
        }

        return Command::SUCCESS;
    }
}

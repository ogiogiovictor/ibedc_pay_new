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
        if (File::exists($logPath)) {
            // Clear the log file by setting its contents to an empty string
            File::put($logPath, '');
            $this->info('Log file cleared successfully.');

             if(File::exists($logPath2)) {
                File::put($logPath2, '');
                File::put($logPath3, '');
             }
        } else {
            $this->info('Log file does not exist.');
        }

        return Command::SUCCESS;
    }
}

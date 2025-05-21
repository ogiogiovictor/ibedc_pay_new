<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MIDDLEWARE\Company;
use Illuminate\Support\Facades\Mail;  // Import Mail facade
use App\Mail\LowBalanceAlert; 


class TopUpWallet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:top-up-wallet';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to check low wallet balances and send email notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('***** Checking for low wallet balance *************');
        $checkbalance = Company::where("status", 1)->get();

        if($checkbalance->isEmpty()){
            $this->info('***** No Company with the status = 1 *************');
        }

        foreach($checkbalance as $check) {

            $this->info('***** Checking '.$check->name. '| Balance:' .$check->balance);
            if($check->paytype == 'wallet' && $check->balance <= 5000000 && in_array($check->id, [5, 19])){


                $this->info('***** Sending Email to Aggreegators *************');
                //send email to the aggregators
                 // Send the email
                 if($check->id == 5) {
                    Mail::to(['tosin.akinwunmi@fetslimited.com', 'info@fetslimited.com', 'timilehin.ayeni@fetslimited.com', 'thomas.attah@fetslimited.com', 'clement.asibeluo@fetslimited.com']) // Send to the relevant aggregator email
                    ->send(new LowBalanceAlert($check)); // Use the mailable class to send the email
                 }
                

                 $this->info('***** Email Sent to Aggregators *************');
             // Alternatively, if you need to send to multiple recipients, you can do:
             // Mail::to(['aggregator1@example.com', 'aggregator2@example.com'])
             //     ->send(new LowBalanceAlert($check));
            }
        }
    }
}

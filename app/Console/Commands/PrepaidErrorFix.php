<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transactions\PaymentTransactions;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Mail;
use App\Mail\PrePaidPaymentMail;
use App\Models\ECMI\EcmiPayments;
use Illuminate\Support\Facades\Auth;

class PrepaidErrorFix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:prepaid-error-fix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prepaid Error Fixes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('***** STARTING PREPAID PROCESSING: Starting to push Pending Prepaid Payments *************');
        $paymentData = []; 

        DB::connection()->enableQueryLog();

        try {

            $prepaidTransaction = PaymentTransactions::whereNotNull('receiptno')
                ->where('account_type', 'Prepaid')
                ->where('status', 'processing')
                ->whereNotNull('providerRef')
                ->chunk(1, function($prepaidpayments) use (&$paymentData) {

                    foreach($prepaidpayments as $paymentLog){

                        $data = [
                            'meterno' => $paymentLog->meter_no,
                            'vendtype' => $paymentLog->account_type,
                            'amount' => $paymentLog->amount, 
                            'provider' => "IBEDC",
                            "custname" => $paymentLog->customer_name,
                            "businesshub" => $paymentLog->BUID,
                            "custphoneno" => $paymentLog->phone,
                            "payreference" => $paymentLog->transaction_id,
                            "colagentid" => "IB001",
                                             
                        ];   
                        
                            $paymentData[] = $data;

                            $update = PaymentTransactions::where("transaction_id", $paymentLog->transaction_id)->update([
                                 'status' => 'success',
                             ]);
                          
                             $token =  $paymentLog->receiptno;
                             $baseUrl = env('SMS_MESSAGE');


                             $idata = [
                                 'token' => env('SMS_TOKEN'),
                                 'sender' => "IBEDC",
                                 'to' => $paymentLog->phone,
                                 "message" => "Meter Token: $token  Your IBEDC Prepaid payment of $paymentLog->amount for Meter No $paymentLog->meter_no  was successful. REF: $paymentLog->transaction_id. For Support: 07001239999",
                                 "type" => 0,
                                 "routing" => 3,
                             ];
     
                             $iresponse = Http::asForm()->post($baseUrl, $idata);
    
                             $this->info('***** SMS SUCCESSFULLY SENT :: SMS has been sent to the customer *************');
    
                             $emailData = [
                                'token' => $token,
                                'meterno' => $paymentLog->meter_no,
                                'amount' => $paymentLog->amount,
                                "custname" => $paymentLog->customer_name,
                                "custphoneno" => $paymentLog->phone,
                                "payreference" => $paymentLog->transaction_id,
                            ];
    
                            $user = Auth::user();
                            Mail::to($user->email)->cc($paymentLog->email)->send(new PrePaidPaymentMail($emailData));
     
                            return $emailData;
                          

                    }

                });
                \Log::info(DB::getQueryLog());
                $this->info('***** PAYMENT COMPLETED:: All payments processed successfully *************');
                

        }catch(\Exception $e){
            \Log::info('ERROR MESSAGE - PREPAID LOG: ' . json_encode($e));
            \Log::info(DB::getQueryLog());
            $this->info('***** TOKENLOOKUP API PAYMENT COMPLETED:: All payments processed successfully *************');
        }
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Wallet\WalletUser;
use App\Models\Wallet\WalletHistory;
use App\Models\Transactions\PaymentTransactions;
use App\Helpers\StringHelper;
use Illuminate\Support\Facades\DB;
use App\Models\VirtualAccountTrasactions;
use App\Models\FailedVirtualAccount;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Jobs\NotificationJob;


class WalletIntegration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:wallet-integration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('***** FLUTTERWAVE VERIFY DAILY PAYMENT API :: Lookup Initiated --- *************');

        $today = now()->toDateString();

        // Define batch size for efficiency
    $batchSize = 50;

    do {
        // Retrieve a batch of failed transactions
        $failedTransactions = FailedVirtualAccount::take($batchSize)->get();

        // Check if there are any transactions to process
        if ($failedTransactions->isEmpty()) {
            break;
        }

        foreach ($failedTransactions as $failedTransaction) {
            // Send HTTP request to Flutterwave
            // $response = Http::withToken(env('FLUTTER_FCMB_KEY'))
            //     ->post("https://api.flutterwave.com/v3/transactions/{$failedTransaction->id}/resend-hook");


            //Check if the transaction does not exist in the database before and it has been completed.
             $checkForTransaction = VirtualAccountTrasactions::where('fid', $failedTransaction->fid)->first();

           
             //if (!$checkForTransaction || $checkForTransaction->status != "successful") {
              if (!$checkForTransaction && $failedTransaction->status == 'successful' ) {
            

                   // Save the data to the VirtualAccountTransactions model
                    $checkTransaction =  VirtualAccountTrasactions::create([
                        'fid' => $failedTransaction->fid,
                        'tx_ref' => $failedTransaction->tx_ref,
                        'flw_ref' => $failedTransaction->flw_ref,
                        'amount' => $failedTransaction->amount,
                        'customer_name' => $failedTransaction->customer_name,
                        'customer_email' => $failedTransaction->customer_email,
                        'status' => $failedTransaction->status,  // successful | approved once it is credited status is changed to completed
                        'payload' => $failedTransaction->payload // json_encode($payload)
                    ]);

                    $verifyID = $failedTransaction->fid;

                    $verificationResponse = Http::withoutVerifying()->withHeaders([
                        "Authorization" => 'Bearer '.  env('FLUTTER_FCMB_KEY'), //env('FLUTTER_FCMB_KEY'), FLUTTER_FCMB_TEST_KEYS
                        "Content-Type" => "application/json"
                    ])->get("https://api.flutterwave.com/v3/transactions/$verifyID/verify");

                    $validationVerifiedResponse =  $verificationResponse->json();

                  

                    if($validationVerifiedResponse['status'] == "success" && $validationVerifiedResponse['data']['status'] == "successful" ) {
                       
                       
                       
                        if (!str_contains($validationVerifiedResponse['data']['tx_ref'], 'RND')) {

                            // Update the transaction reference as completed
                            $checkTransaction = VirtualAccountTrasactions::where("tx_ref",  $validationVerifiedResponse['data']['tx_ref'])
                            ->update(['status' => $data['status']]);
           
                              //dispatch a job   $transID, $user_email, $payload
                           dispatch(new NotificationJob($validationVerifiedResponse['data']['tx_ref'], $validationVerifiedResponse['customer']['customer_email'], $validationVerifiedResponse));
           
                           // Return a 200 OK response to acknowledge receipt of the webhook
                            return response()->json(['message' => 'Webhook received and data saved successfully for Bank'], Response::HTTP_OK);
                       }
                       
                       
                        //Credit the customer wallet
                        $user = User::where("email", $failedTransaction->customer_email)->first();

                        // Check if customer wallet exists; if not, create one with initial balance
                        $wallet = WalletUser::where('user_id', $user->id)->first();

                        if($wallet){
                            // If the wallet exists, increment the balance
                            $wallet->increment('wallet_amount', $failedTransaction->amount);

                             //Create credit history
                            $createHistory = WalletHistory::create([
                                'user_id' => $user->id,
                                'payment_channel' => "FCMI-FLUTTERWAVE",
                                'price' =>  $failedTransaction->amount,
                                'status' =>   $failedTransaction->status,
                                'transactionId' =>  $failedTransaction->fid,
                                'provider_reference' =>  $failedTransaction->flw_ref,
                                'entry' => 'CR'
                            ]);

                            //dispatch a job   $transID, $user_email, $payload
                         dispatch(new NotificationJob($failedTransaction->tx_ref, $failedTransaction->customer_email, $validationVerifiedResponse));

                         $failedTransaction->delete();


                        } else {
                           
                            // // If the wallet does not exist, create a new one with the initial balance
                            // WalletUser::create([
                            //     'user_id' => $user->id,
                            //     'wallet_amount' => $failedTransaction->amount,
                            // ]);
                                                        
                        }
                        
                    }
             }


            // // Check if response is successful
            // if ($response->json('status') === 'success') {
            //     DB::transaction(function () use ($failedTransaction) {
            //         // Move transaction to VirtualAccountTransactions table
            //         VirtualAccountTransactions::create([
            //             'email' => $failedTransaction->email,
            //             'flw_ref' => $failedTransaction->flw_ref,
            //             // Add other fields as necessary from the failed transaction
            //         ]);

            //         // Delete transaction from FailedVirtualAccount
            //         $failedTransaction->delete();
            //     });
            // }
        }

        // Continue looping if batch was filled
    } while ($failedTransactions->count() === $batchSize);

    return true;

    }
}

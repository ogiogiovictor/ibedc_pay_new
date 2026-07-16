<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Wallet\WalletHistory;
use App\Models\Wallet\WalletUser;
use App\Models\User;
use App\Models\VirtualAccount;
use App\Models\VirtualAccountTrasactions;
use App\Models\Transactions\PaymentTransactions;
use App\Models\WalletRefund;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class WalletDetaisls extends Component
{
    public $id;
    public $user_id;
    public $data;


    public function mount() {

        // Fetch wallet transaction
        $walletTransaction = WalletHistory::find($this->id);

        // Fetch user details
        $user = User::find($this->user_id);

        // Initialize data array
        $this->data = [
            'walletTransaction' => $walletTransaction,
            'user' => $user,
            'wallet' => null,
            'virtualAccount' => null,
            'virtualAccountTransactions' => [],
            'userwallethistory' => [],
            'paymenthistory' => []
        ];

        if ($user) {
            // Fetch wallet details
            $this->data['wallet'] = WalletUser::where('user_id', $this->user_id)->first();

            // Fetch virtual account details
            $this->data['virtualAccount'] = VirtualAccount::where('user_id', $this->user_id)->orderby("created_at", "desc")->first();

            // Fetch virtual account transactions
           // $this->data['virtualAccountTransactions'] = VirtualAccountTrasactions::where('customer_email', $user->email)->orderby("created_at", "desc")->take(500)->get();

            $this->data['userwallethistory'] = WalletHistory::where('user_id', $this->user_id)->orderby("created_at", "desc")->take(1000)->get();

            $this->data['paymenthistory'] = PaymentTransactions::whereIn("status", ['processing', 'failed', 'success', 'cancelled'])->where('user_id', $this->user_id)->orderby("created_at", "desc")->take(500)->get();


             // Fetch virtual account transactions
            $virtuals = VirtualAccountTrasactions::where('customer_email', $user->email)
                ->orderBy("created_at", "desc")
                ->take(500)
                ->get();

            $walletBalance = $this->data['wallet']->wallet_amount ?? 0;

              /** 
             * --------------------------
             * REFUND BUTTON LOGIC
             * --------------------------
             */

            if ($virtuals->count() == 1) {

                // Only one transaction → show refund
                $virtuals[0]->show_refund = true;

            } else {

                // More than one → find the highest amount > wallet balance
                $candidate = $virtuals
                    ->where('amount', '>', $walletBalance)
                    ->sortByDesc('amount')
                    ->first();

                foreach ($virtuals as $tx) {
                    $tx->show_refund = ($candidate && $tx->id == $candidate->id);
                }
            }

            $this->data['virtualAccountTransactions'] = $virtuals;

        }


     //  dd($this->data);

    }


    public function refund($amount, $user_id, $account_name, $customer_email, $user_email)
    {
        $user = User::find($user_id);

        if (!$user) {
            return session()->flash('error', 'User not found for refund.');
        }

        if (!$amount || !$user_id || !$account_name || !$customer_email || !$user_email) {
            return session()->flash('error', 'Missing required parameters for refund.');
        }

        if (!is_numeric($amount) || $amount <= 0) {
            return session()->flash('error', 'Invalid refund amount.');
        }

        if ($customer_email !== $user_email) {
            return session()->flash('error', 'Customer email does not match user email.');
        }

       
        // Find matching VA transaction
        $transactions = VirtualAccountTrasactions::where('customer_email', $customer_email)->get();
        $match = $transactions->firstWhere('amount', '>=', $amount);

         $checkwalletRefund = WalletRefund::where('log_by', $match->fid)->first();
        if ($checkwalletRefund) {
            return session()->flash('error', 'Refund has already been processed for this transaction.');
        }

         $checkCustomerEmailExist = WalletRefund::where('customer_email', $customer_email)->first();
        if ($checkCustomerEmailExist) {
            return session()->flash('error', 'Refund has already been processed for this transaction.');
        }

        if (!$match) {
            return session()->flash('error', 'No transaction found with sufficient balance to refund.');
        }

        $flutterwaveSecret = "FLWSECK-effa327dab3411ddeb7730dd0e5e38bf-191d6565d8dvt-X";

        $response = Http::withToken($flutterwaveSecret)->post(
            "https://api.flutterwave.com/v3/transactions/{$match->fid}/refund",
            [
                "amount" => $amount,
                "comment" => "Refund from IBEDCPay for your wallet balance for $account_name",
                "callbackurl" => "https://ipay.ibedc.com",
            ]
        );

        $responseBody = $response->json();

        //dd($responseBody['status']);

        /** ----------------------------
         *  HANDLE FLUTTERWAVE FAILURES
         * ---------------------------- */

         if ($responseBody['status']  == 'success') {

            $this->logRefund($user_id, $user_email, $customer_email, $amount, $match->fid, $responseBody, $user->phone);

            return session()->flash('success', 'Refund Successfully Initiated.');
            return redirect()->refresh();
        }
       

        // 1. Operation not permitted
        if (
            ($responseBody['status'] ?? null) === 'error' &&
            ($responseBody['data'] ?? '') === 'Error: Operation not permitted'
        ) {
            return session()->flash('error', 'Refund failed: Operation not permitted');
        }

        // 2. Transaction already refunded  //Error: Sum of total refunds cannot be greater than the charged amount
        //Refund failed: Service error
        if (
            ($responseBody['message'] ?? '') === 'Transaction already refunded' ||
            ($responseBody['data'] ?? '') === 'Error: Transaction already fully refunded' ||
            ($responseBody['data'] ?? '') === 'Refund failed: Service error' ||
            ($responseBody['message'] ?? '') === 'Refund failed: Service error' ||
            ($responseBody['data'] ?? '') === 'Error: Sum of total refunds cannot be greater than the charged amount'
        ) {
            $this->logRefund($user_id, $user_email, $customer_email, $amount, $match->fid, $responseBody, $user->phone);
            return session()->flash('error', 'Refund failed: Transaction already fully refunded');
        }

        /** -----------------------------------------------------
         *  SUCCESS CONDITIONS (INCLUDING YOUR SPECIAL CASE)
         * ----------------------------------------------------- */

        $specialCase = "A partial refund is currently processing on this transaction. Please, try again in 2 mins.";

        if (
            ($responseBody['status'] ?? '') === 'success' ||
            (($responseBody['data'] ?? '') === $specialCase)
        ) {

            $this->logRefund($user_id, $user_email, $customer_email, $amount, $match->fid, $responseBody, $user->phone);

            return session()->flash('success', 'Refund Successfully Initiated.');
            return redirect()->refresh();
        }

        $msg = $responseBody['message'] ?? 'Unknown refund error';
         $this->logRefund($user_id, $user_email, $customer_email, $amount, $match->fid, $responseBody, $user->phone);
         return session()->flash('error', 'Refund failed: ' . $msg);
         return redirect()->refresh();
        // /** Default failure */
        // $msg = $responseBody['message'] ?? 'Unknown refund error';
        // return session()->flash('error', 'Refund failed: ' . $msg);
    }


    /**
     * Helper function to log refund
     */
    private function logRefund($user_id, $user_email, $customer_email, $amount, $fid, $responseBody, $phone)
    {
        WalletRefund::create([
            'user_id' => $user_id,
            'user_email' => $user_email,
            'customer_email' => $customer_email . " | " . auth()->user()->id,
            'wallet_amount' => $amount,
            'log_by' => $fid,
            'response' => json_encode($responseBody),
        ]);

          // Deduct from wallet
        $wallet = WalletUser::where('user_id', $user_id)->first();
        if ($wallet) {

            // New Logic → Prevent Negative Balance
            $newBalance = $wallet->wallet_amount - $amount;

            if ($newBalance < 0) {
                $wallet->wallet_amount = 0;   // Force balance to zero
            } else {
                $wallet->wallet_amount = $newBalance;
            }

            $wallet->save();
        }
            // if ($wallet) {
            //     $wallet->wallet_amount -= $amount;
            //     $wallet->save();
            // }

                   //  Send email notification to user about wallet refund
                     try {
                            $to = $user_email;
                            $cc = ['Victor.Otunuga@ibedc.com', 'adebayo.oyebamiji@ibedc.com', 'victor.ogiogio@ibedc.com', 'Thelma.Okunorobo@ibedc.com', 'mayowa.ariyo@ibedc.com', 'babatunde.bodunde@ibedc.com' ]; // add more if needed

                            Mail::raw(
                                "Dear {$user_email},\n\nYour account with wallet amount of ₦" . number_format($amount, 2) . " has been successfully refunded\n\nThank you.\n\n IBEDCPay.",
                                function ($message) use ($to, $cc) {
                                    $message->to($to)
                                            ->bcc($cc)
                                            ->subject('Wallet Amount Refunded Successfully');
                                }
                            );
                        } catch (\Exception $e) {
                            \Log::error('Mail sending failed: ' . $e->getMessage());
                        }

             //   Send SMS notification to user about wallet refund
              //   Send SMS to Customer
                // $baseUrl = env('SMS_MESSAGE');
                // $idata = [
                //     'token' => env('SMS_TOKEN2'),
                //     'sender' => "IBEDC",
                //     'to' => $phone,
                //     "message" => "Dear Customer, your wallet amount with IBEDCpay has been refunded back to your account Amount - . $amount. For support, call 07001239999.",
                //     "type" => 0,
                //     "routing" => 3,
                // ];
                // $iresponse = Http::asForm()->post($baseUrl, $idata);



    }




    // public function refund($amount, $user_id, $account_name, $customer_email, $user_email) {

    //     $user = User::find($user_id);

    //     if(!$user) {
    //         session()->flash('error', 'User not found for refund.');
    //         return;
    //     }


    //     if(!$amount || !$user_id || !$account_name || !$customer_email || !$user_email) {
    //         session()->flash('error', 'Missing required parameters for refund.');
    //         return;
    //     }

    //     if(!is_numeric($amount) || $amount <= 0) {
    //         session()->flash('error', 'Invalid refund amount.');
    //         return;
    //     }

    //     if($customer_email !== $user_email) {
    //         session()->flash('error', 'Customer email does not match user email. Amount Cannot be refunded. Contact Support.');
    //         return;
    //     }

    //     // Get the virtual account transaction id you want to refund
    //     $transactions = VirtualAccountTrasactions::where('customer_email', $customer_email)->get();

    //      // --- LOOP TO FIND THE FIRST TRANSACTION THAT CAN COVER THE REFUND ---
    //     $match = null;

    //     foreach($transactions as $tx) {
    //         if($tx->amount >= $amount) {
    //             $match = $tx;
    //             break; // Stop at the FIRST match
    //         }
    //     }

    //     // If no matching transaction found
    //     if(!$match) {
    //         session()->flash('error', 'No transaction found with sufficient balance to refund.');
    //         return;
    //     }

       
    //     //initiate the refund process here using flutterwave or your payment gateway

    //     $flutterwaveSecret = "FLWSECK-effa327dab3411ddeb7730dd0e5e38bf-191d6565d8dvt-X"; 

    //     $response = Http::withToken($flutterwaveSecret)
    //     ->post("https://api.flutterwave.com/v3/transactions/{$match->fid}/refund", [
    //         "amount" => $amount,
    //         "comment" => "Refund from IBEDCPay for your wallet balance for $account_name",
    //         "callbackurl" => "https://ipay.ibedc.com",
    //     ]);



    //     $responseBody = $response->json();
    //    // dd($responseBody['status']);

    //     if($responseBody['status'] == 'error' && $responseBody['data'] == 'Error: Operation not permitted') {
    //         session()->flash('error', 'Refund failed: '.$responseBody['data'] ?? 'Unknown error');
    //         return;
    //     }


    //     if(isset($responseBody['message']) && $responseBody['data'] === 'Error: Transaction already fully refunded') {

    //          //Log the refund in your database as needed
    //         $log = WalletRefund::create([
    //             'user_id' => $user_id,
    //             'user_email' => $user_email,
    //             'customer_email' => $customer_email ."|". auth()->user()->id,
    //             'wallet_amount' => $amount,
    //             'log_by' => $match->fid,
    //             'response' => json_encode($responseBody),
    //         ]);

            
    //         if($log) {
    //             // Update wallet balance
    //             $wallet = WalletUser::where('user_id', $user_id)->first();
    //             if($wallet) {
    //                 $wallet->wallet_amount = $wallet->wallet_amount - $amount;
    //                 $wallet->save();



    //             // Send email notification to user about wallet refund
    //                 //  try {
    //                 //         $to = $user_email;
    //                 //         $cc = ['Victor.Otunuga@ibedc.com', 'adebayo.oyebamiji@ibedc.com', 'victor.ogiogio@ibedc.com', 'Thelma.Okunorobo@ibedc.com', 'mayowa.ariyo@ibedc.com', 'babatunde.bodunde@ibedc.com' ]; // add more if needed

    //                 //         Mail::raw(
    //                 //             "Dear {user_email},\n\nYour account with wallet amount of ₦" . number_format($amount, 2) . ".\n\nhas been successfully refunded\n\nThank you.",
    //                 //             function ($message) use ($to, $cc) {
    //                 //                 $message->to($to)
    //                 //                         ->bcc($cc)
    //                 //                         ->subject('Wallet Credited Successfully');
    //                 //             }
    //                 //         );
    //                 //     } catch (\Exception $e) {
    //                 //         \Log::error('Mail sending failed: ' . $e->getMessage());
    //                 //     }

    //             // Send SMS notification to user about wallet refund
    //              //Send SMS to Customer
    //             // $baseUrl = env('SMS_MESSAGE');
    //             // $idata = [
    //             //     'token' => env('SMS_TOKEN2'),
    //             //     'sender' => "IBEDC",
    //             //     'to' => $user->phone,
    //             //     "message" => "Dear Customer, your wallet amount with IBEDCpay has been refunded back to your account Amount - . $amount. For support, call 07001239999.",
    //             //     "type" => 0,
    //             //     "routing" => 3,
    //             // ];
    //             // $iresponse = Http::asForm()->post($baseUrl, $idata);


    //             }

    //             //send notification to user about refund
    //             //SMS logic here

    //         }

    //         session()->flash('error', 'Refund failed: Transaction already fully refunded');
    //         return;

    //     }

    //      // Handle Flutterwave Response
    //     if (($response->successful() && $responseBody['status'] === 'success') 
    //     || (isset($responseBody['message']) 
    //         && $responseBody['data'] === "A partial refund is currently processing on this transaction. Please, try again in 2 mins.")) {


    //         //Log the refund in your database as needed
    //         $log = WalletRefund::create([
    //             'user_id' => $user_id,
    //             'user_email' => $user_email,
    //             'customer_email' => $customer_email ."|". auth()->user()->id,
    //             'wallet_amount' => $amount,
    //             'log_by' => $match->fid,
    //             'response' => json_encode($responseBody),
    //         ]);

    //         if($log) {
    //             // Update wallet balance
    //             $wallet = WalletUser::where('user_id', $user_id)->first();
    //             if($wallet) {
    //                 $wallet->wallet_amount = $wallet->wallet_amount - $amount;
    //                 $wallet->save();



    //             // Send email notification to user about wallet refund
    //                 //  try {
    //                 //         $to = $user_email;
    //                 //         $cc = ['Victor.Otunuga@ibedc.com', 'adebayo.oyebamiji@ibedc.com', 'victor.ogiogio@ibedc.com', 'Thelma.Okunorobo@ibedc.com', 'mayowa.ariyo@ibedc.com', 'babatunde.bodunde@ibedc.com' ]; // add more if needed

    //                 //         Mail::raw(
    //                 //             "Dear {user_email},\n\nYour account with wallet amount of ₦" . number_format($amount, 2) . ".\n\nhas been successfully refunded\n\nThank you.",
    //                 //             function ($message) use ($to, $cc) {
    //                 //                 $message->to($to)
    //                 //                         ->bcc($cc)
    //                 //                         ->subject('Wallet Credited Successfully');
    //                 //             }
    //                 //         );
    //                 //     } catch (\Exception $e) {
    //                 //         \Log::error('Mail sending failed: ' . $e->getMessage());
    //                 //     }

    //             // Send SMS notification to user about wallet refund
    //              //Send SMS to Customer
    //             // $baseUrl = env('SMS_MESSAGE');
    //             // $idata = [
    //             //     'token' => env('SMS_TOKEN2'),
    //             //     'sender' => "IBEDC",
    //             //     'to' => $user->phone,
    //             //     "message" => "Dear Customer, your wallet amount with IBEDCpay has been refunded back to your account Amount - . $amount. For support, call 07001239999.",
    //             //     "type" => 0,
    //             //     "routing" => 3,
    //             // ];
    //             // $iresponse = Http::asForm()->post($baseUrl, $idata);


    //             }

    //             //send notification to user about refund
    //             //SMS logic here

    //         }

    //         session()->flash('success', 'Refund Successfully Initiated.');
    //         return;

    //     }


    //     session()->flash('error', 'Refund failed:  Flutterwave Error: - '.$responseBody['message'] ?? 'Unknown error');
    //     //dd("Refund initiated for transaction with FLW_REF: " . $match);
    // }

    public function render()
    {
        return view('livewire.wallet-detaisls');
    }
}

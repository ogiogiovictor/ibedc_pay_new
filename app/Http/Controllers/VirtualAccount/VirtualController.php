<?php

namespace App\Http\Controllers\VirtualAccount;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use App\Jobs\VirtualAccountJob;
use App\Models\VirtualAccount;
use App\Helpers\StringHelper;
use Illuminate\Support\Facades\Auth;
use App\Models\VirtualAccountTrasactions;
use App\Models\User;
use App\Models\Wallet\WalletUser;
use App\Models\Wallet\WalletHistory;
use App\Models\FailedVirtualAccount;
use App\Jobs\NotificationJob;
use Illuminate\Support\Facades\Log;
use App\Services\AuditLogService;

class VirtualController extends BaseAPIController
{
    public function createVirtualAccount(Request $request){

        $validatedData =   $request->validate([
            'email' => 'required|string|max:255',
            'bvn' => 'required|string|max:12',
            'phonenumber' => 'required|string',
            'firstname' => 'required',
            'lastname' => 'required',
        ]);

        $otherData = [
            'is_permanent' => true,
            'narration' => "Virtual Account Creation for " . $validatedData['firstname'] . ' ' . $validatedData['lastname'],
        ];

    
        // Merge the validated data with other data
        $newRequest = array_merge($validatedData, $otherData); // for array
     //   $newRequest = $validatedData->merge($otherData); // for object

        $checkEmail = User::where("email", $validatedData['email'])->first();

        $updateEmail = str_starts_with($validatedData['email'], 'default') || str_starts_with($validatedData['email'], 'noemail');

        if($updateEmail) {
            return $this->sendError("Only users with registered email /password can create wallet", 'ERROR', Response::HTTP_UNAUTHORIZED);  
        }

        if(!$checkEmail) {
            return $this->sendError("User Email Does Not Exist In Our System", 'ERROR', Response::HTTP_UNAUTHORIZED);
        }
       
        $response = Http::withoutVerifying()->withHeaders([
            'Authorization' => 'Bearer '.  env('FLUTTER_FCMB_KEY'), //env('FLUTTER_FCMB_KEY'),  FLUTTER_FCMB_TEST_KEYS
        ])->post("https://api.flutterwave.com/v3/virtual-account-numbers", $newRequest);

         $newResponse =  $response->json();

        $requestRef = StringHelper::generateUUIDReference();
        $user = Auth::user();

        // Properly access 'status' and 'data' keys from the response
    if ($newResponse['status'] == "success" && isset($newResponse['data']['response_code']) && $newResponse['data']['response_code'] == "02") {

          $account =   VirtualAccount::create([
                'transaction_ref' => $newResponse['data']['flw_ref'], 
                'account_no' => $newResponse['data']['account_number'], 
                 'contract_code' => $newResponse['data']['order_ref'], 
                 'account_reference' => $requestRef, 
                 'account_name'=> $validatedData['firstname'] . ' ' . $validatedData['lastname'],  
                 'customer_email' => $validatedData['email'],   
                 'bank_name' => $newResponse['data']['bank_name'],   
                'bank_code' => $newResponse['data']['response_code'],   
                'account_type' => "FCMB",   
                'status' => isset($newResponse['data']['account_status']) ? $newResponse['data']['account_status'] : "active",  
                'user_id' => $user->id,
                'bvn' => $validatedData['bvn']
            ]);

            //dispatch(new VirtualAccountJob($newResponse, $user));
            dispatch(new VirtualAccountJob($account, $user));

            $audit_description = "Virtual Account Created for ". $validatedData['firstname'] . ' ' . $validatedData['lastname']. ' with email '. $validatedData['email']
            . ' and account no '. $newResponse['data']['account_number'];
            AuditLogService::logAction('Virtual Account', 'customer',  $audit_description, $user->id, 200);

            return $this->sendSuccess( [
                'account' => $account,
                'message' => 'Virtual Account Successfully Created.',
            ], 'Account Created', Response::HTTP_OK);

        }

        return $this->sendError($newResponse, 'ERROR', Response::HTTP_UNAUTHORIZED);
    }







    public function handleFlutterwaveWebhookFCMB(Request $request){

    //    $getLog =  Log::warning('First Logs', ['First Log' => $request]);

        // Retrieve the secret hash from the environment file
        $secretHash = env('FLUTTERWAVE_SECRET_HASH');

        // Get the hash from the incoming request's header
        $incomingHash = $request->header('verif-hash');

        // Verify if the incoming hash matches the secret hash
        if (!$incomingHash || ($incomingHash !== $secretHash)) {
            // Log for debugging or auditing
            Log::warning('Invalid Flutterwave Webhook Request: Invalid Hash', ['incomingHash' => $incomingHash]);
            // Return a 403 Forbidden response
            return response()->json(['error' => 'Invalid webhook signature'], Response::HTTP_FORBIDDEN);
        }


       
        // Process the webhook payload
        $payload = $request->all();

        Log::warning('Second Logs', ['First Log' => $payload]);

        //Check if the transaction does not exist in the database before and it has been completed.
       // $checkForTransaction = VirtualAccountTrasactions::where('tx_ref', $payload['data']['tx_ref'])->first();

        $checkForTransaction = VirtualAccountTrasactions::where('flw_ref', $payload['data']['flw_ref'])->first();
        
        if($checkForTransaction){

            $data = $payload['data'];
            
            // Save the data to the VirtualAccountTransactions model
            $checkTransaction =  FailedVirtualAccount::create([
            'fid' => $data['id'],
            'tx_ref' => $data['tx_ref'],
            'flw_ref' => $data['flw_ref'],
            'amount' => $data['amount'],
            'customer_name' => $data['customer']['name'],
            'customer_email' => $data['customer']['email'],
            'status' => $data['status'],  // successful | approved once it is credited status is changed to completed
            'payload' => json_encode($payload)
           ]);

            //dispatch a job   $transID, $user_email, $payload
            dispatch(new NotificationJob($data['tx_ref'], $data['customer']['email'], $payload));

            return response()->json(['error' => 'Transaction ID Alread Exist'], Response::HTTP_FORBIDDEN);

        }

        // Here you can handle the payload as needed. For example:
        Log::info('Valid Flutterwave Webhook Received', ['payload' => $payload]);

        // Check if the event type is 'charge.completed'
        if ($payload['event'] === 'charge.completed' && $payload['data']['status'] == "successful" ) {

            $data = $payload['data']; // Extract the 'data' array from the payload

            // Save the data to the VirtualAccountTransactions model
           $checkTransaction =  VirtualAccountTrasactions::create([
                'fid' => $data['id'],
                'tx_ref' => $data['tx_ref'],
                'flw_ref' => $data['flw_ref'],
                'amount' => $data['amount'],
                'customer_name' => $data['customer']['name'],
                'customer_email' => $data['customer']['email'],
                'status' => $data['status'],  // successful | approved once it is credited status is changed to completed
                'payload' => json_encode($payload)
            ]);

            // Log the received payload for debugging
            Log::info('Valid Flutterwave Webhook Received and Saved', ['payload' => $payload]);

            //Verify the transaction 
            $verifyID = $data['id'];

            $verificationResponse = Http::withoutVerifying()->withHeaders([
                "Authorization" => 'Bearer '.  env('FLUTTER_FCMB_KEY'), //env('FLUTTER_FCMB_KEY'), FLUTTER_FCMB_TEST_KEYS
                "Content-Type" => "application/json"
            ])->get("https://api.flutterwave.com/v3/transactions/$verifyID/verify");

            
            $validationVerifiedResponse =  $verificationResponse->json();

            //Check if the transaction has RND
            //$transactionReference = $validationVerifiedResponse['data']['tx_ref'];

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
                $user = User::where("email", $validationVerifiedResponse['customer']['email'])->first();

                // Check if customer wallet exists; if not, create one with initial balance
                $wallet = WalletUser::where('user_id', $user->id)->first();

                if($wallet && str_contains($validationVerifiedResponse['data']['tx_ref'], 'RND') ){
                    // If the wallet exists, increment the balance
                    $wallet->increment('wallet_amount', $validationVerifiedResponse['data']['amount']);
                }
                // } else {
                //      // If the wallet does not exist, create a new one with the initial balance
                //      WalletUser::create([
                //         'user_id' => $user->id,
                //         'wallet_amount' =>  $validationVerifiedResponse['data']['amount'],
                //     ]);
                // }

                // Update the transaction reference as completed
                $checkTransaction = VirtualAccountTrasactions::where("tx_ref",  $validationVerifiedResponse['data']['tx_ref'])
                ->update(['status' => $data['status']]);

                //Create credit history
                $createHistory = WalletHistory::create([
                    'user_id' => $user->id,
                    'payment_channel' => "FCMI-FLUTTERWAVE",
                    'price' => $validationVerifiedResponse['data']['amount'],
                    'status' =>  $validationVerifiedResponse['data']['status'],
                    'transactionId' =>  $data['tx_ref'],
                    'entry' => 'CR'
                ]);


                //dispatch a job   $transID, $user_email, $payload
                dispatch(new NotificationJob($validationVerifiedResponse['data']['tx_ref'], $validationVerifiedResponse['customer']['customer_email'], $validationVerifiedResponse));

                // Return a 200 OK response to acknowledge receipt of the webhook
                 return response()->json(['message' => 'Webhook received and data saved successfully'], Response::HTTP_OK);

            } else {

                return response()->json(['message' => 'Error Processing Webhook'], Response::HTTP_BAD_REQUEST);
            }

            

        } else {

            Log::warning('Invalid Flutterwave Webhook Request: Invalid Hash', ['invalid WebhookRequest' => $payload]);
            // Return a 403 Forbidden response
            return response()->json(['error' => 'Invalid Request'], Response::BAD_REQUEST);

        }
       
    }




    public function handleSuccessWebHookDespatch(Request $request){
        
         // Extract the required data from the request
        $tx_ref = $request->input('tx_ref');
        $customer_email = $request->input('customer');
        $payload = $request->input('customer');

        // Dispatch the job with the extracted data
        dispatch(new NotificationJob($tx_ref, $customer_email, $payload));

        //dispatch a job   $transID, $user_email, $payload
        //dispatch(new NotificationJob($data['tx_ref'], $data['customer']['email'], $payload));
    }






    public function handleFailedWebhookFCMB(Request $request){

         // Retrieve the secret hash from the environment file
         $secretHash = env('FLUTTERWAVE_SECRET_HASH');

         // Get the hash from the incoming request's header
         $incomingHash = $request->header('verif-hash');
 
         // Verify if the incoming hash matches the secret hash
         if (!$incomingHash || ($incomingHash !== $secretHash)) {
             // Log for debugging or auditing
             Log::warning('Invalid Flutterwave Webhook Request: Invalid Hash', ['incomingHash' => $incomingHash]);
             // Return a 403 Forbidden response
             return response()->json(['error' => 'Invalid webhook signature'], Response::HTTP_FORBIDDEN);
         }
 
         // Process the webhook payload
         $payload = $request->all();
 

         // Save the data to the VirtualAccountTransactions model
         $checkTransaction =  FailedVirtualAccount::create([
            'fid' => $data['id'],
            'tx_ref' => $data['tx_ref'],
            'flw_ref' => $data['flw_ref'],
            'amount' => $data['amount'],
            'customer_name' => $data['customer']['name'],
            'customer_email' => $data['customer']['email'],
            'status' => $data['status'],  // successful | approved once it is credited status is changed to completed
            'payload' => json_encode($payload)
           ]);

            //dispatch a job   $transID, $user_email, $payload
            dispatch(new NotificationJob($data['tx_ref'], $data['customer']['customer_email'], $payload));

            return response()->json(['message' => 'Webhook transaction failed'], Response::HTTP_OK);

    }



}

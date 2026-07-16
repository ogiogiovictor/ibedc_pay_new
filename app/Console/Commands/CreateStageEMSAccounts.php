<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NAC\PendingAccountCreation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\NAC\UploadHouses;
use App\Models\EMS\BusinessUnit;
use App\Models\EMS\Undertaking;
use App\Models\NAC\DSS;
use App\Models\NAC\ServiceAreaCode;
use Illuminate\Support\Facades\Http;
use App\Jobs\CustomerAccountJob;
use App\Models\NAC\ContinueAccountCreation;
use Illuminate\Support\Facades\Mail;
use App\Services\IbedcPayLogService;
use Illuminate\Support\Facades\Log;
use App\Models\NAC\AccoutCreaction;



class CreateStageEMSAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-stage-ems-accounts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create EMS Account';

    /**
     * Execute the console command.
     */
    public function handle()
    {
         $this->info('🔄 Starting EMS Account Creation process...');


          /**
         * ------------------------------------------------------------
         * STEP 1: DELETE PENDING RECORDS ALREADY IN UPLOAD_HOUSES
         * ------------------------------------------------------------
         */
        $this->info('🧹 Checking PendingAccountCreation against UploadHouses...');

      // 1. Get account numbers that exist in UploadHouses
        $duplicateAccountNos = PendingAccountCreation::whereNotNull('account_no')
            ->whereIn('account_no', function ($query) {
                $query->select('account_no')
                    ->from('upload_houses')
                    ->whereNotNull('account_no');
            })
            ->pluck('account_no')
            ->unique()
            ->values();

        // 2. Log the account numbers
        if ($duplicateAccountNos->isNotEmpty()) {
               $this->info('🗑️ Deleted PendingAccountCreation account numbers:');
                foreach ($duplicateAccountNos as $accNo) {
                    $this->info("   - {$accNo}");  // Use info() here
                }

            // 3. Delete the records
            PendingAccountCreation::whereIn('account_no', $duplicateAccountNos)->delete();
        } else {
            $this->info('✅ No duplicate account numbers found.');
        }


        $pendingAccounts = PendingAccountCreation::orderby('created_at', 'desc')->get();
        // $pendingAccounts = PendingAccountCreation::all();

        if ($pendingAccounts->isEmpty()) {
            $this->info('✅ No pending accounts found.');
            return;
        }



         foreach ($pendingAccounts as $pending) {
            try {
                $this->info("Processing Pending Record ID: {$pending->id}");

                // decode JSON fields
                $account = json_decode($pending->account);
                $uploadHouses = json_decode($pending->upload_houses);
                $servicecode = json_decode($pending->service_code);
                $dss = $pending->dss;
                $accountNo = $pending->account_no;
                $user = $pending->user_email;
                $id = $pending->id;

                // Attempt to create EMS account
                $success = $this->createEMSAccount($accountNo, $account, $uploadHouses, $servicecode, $dss, $id);

                if ($success) {
                    $this->info("✅ Account {$accountNo} successfully created in EMS.");

                    // Retrieve the related models again (Eloquent instances)
                   
                   // $checkaccount = UploadHouses::where("account_no",  $accountNo)->exists();
                   $checkaccount = UploadHouses::where("account_no", $accountNo)->whereNull("account_no")->first();
                    
                    if (!$checkaccount) {


                          try {
                                    $this->finalizeAccount($account, $uploadHouses, $accountNo, $servicecode, $user);

                                } catch (\Exception $e) {
                                    $this->error("⚠️ finalizeAccount failed for Account No: {$accountNo}: " . $e->getMessage());
                                }

                                // Log before deletion
                                $existsBefore = PendingAccountCreation::where('id', $pending->id)->exists();
                                $this->info("Before deletion: " . ($existsBefore ? 'Exists' : 'Not exists'));

                                // Delete the pending record
                                $pending->delete();

                                // Log after deletion
                                $existsAfter = PendingAccountCreation::where('id', $pending->id)->exists();
                                $this->info("After deletion: " . ($existsAfter ? 'Exists' : 'Not exists'));
                                $this->info("🗑️ Deleted PendingAccountCreation record ID: {$pending->id}, Account No: {$accountNo}");
                            
                         // Finalize the process
                        // $this->finalizeAccount($account, $uploadHouses, $accountNo, $servicecode, $user);

                        // // Log success
                        // $this->info("***** ONE TRANSACTION UPDATED AS SUCCESSFUL ******* " . $pending->tracking_id);

                        // // Delete the pending record
                        // $existsBefore = PendingAccountCreation::where('id', $pending->id)->exists(); // Should be true
                        // $pending->delete();
                        // $existsAfter = PendingAccountCreation::where('id', $pending->id)->exists();  // Should be false

                        // $this->info("Before deletion: " . ($existsBefore ? 'Exists' : 'Not exists'));
                        // $this->info("After deletion: " . ($existsAfter ? 'Exists' : 'Not exists'));
                                                    
                    } else {
                                $this->warn("⚠️ Skipping finalization — UploadHouses already has Account No: {$accountNo}");
                     }

                   
                } else {
                    $this->error("❌ Failed to create EMS account for {$accountNo}.");
                }
            } catch (\Exception $e) {
                Log::error("Error creating EMS account: " . $e->getMessage(), [
                    'pending_id' => $pending->id,
                ]);
                $this->error("⚠️ Error processing pending ID {$pending->id}: " . $e->getMessage());
            }
        }

        $this->info('🎉 EMS Account Creation process completed.');

    }




     /**
     * Create EMS Account
     */
    private function createEMSAccount($accountNo, $account, $uploadHouses, $servicecode, $dss, $id)
    {
        $url = "http://192.168.15.157:9549/AccountGenerator/webresources/account/save/customer/114/FX321G9D"; // live API

        $landlordInfo = ContinueAccountCreation::where("tracking_id", $uploadHouses->tracking_id)->first();

         // ✅ Use organisation_name if it's not null or empty, else use landlord_othernames
        if (!empty($landlordInfo->organisational_name)) {
            $fname = $landlordInfo->organisational_name;
            $sname = ""; // No surname for organisations
        
        } else {
            $fname = $landlordInfo->landlord_othernames ?? ($account->firstname ?? '');
            $sname = $landlordInfo->landlord_surname ?? ($account->surname ?? '');
        }


        $lat = substr($uploadHouses->latitude ?? '', 0, 10);
        $lng = substr($uploadHouses->longitude ?? '', 0, 10);

        $data = [
            "accountNo" => $accountNo,
            "meterNo" => "",
            "surname" =>  $sname,
            "firstName" => $fname,
            "otherNames" => "",
            "email" => $account->email ?? '',
            "serviceAddress1" => ($uploadHouses->house_no ?? '') . ' ' . ($uploadHouses->full_address ?? ''),
            "serviceAddress2" => $uploadHouses->business_hub ?? '',
            "serviceAddressCity" => $uploadHouses->service_center ?? '',
            "serviceAddressState" => $uploadHouses->region ?? '',
            "tariffID" => $uploadHouses->tarrif ?? '',
            "arrears" => '',
            "mobile" => $landlordInfo->landlord_telephone ?? ($account->phone ?? ''),
            //"gisCoordinate" => ($uploadHouses->latitude ?? '') . ',' . ($uploadHouses->longitude ?? ''),
            "gisCoordinate" => ($lat ?? '') . ',' . ($lng ?? ''),
            "buid" => $servicecode->BUID ?? '',
            "distributionID" => $dss ?? '',
            "accessGroup" => "Administrator"
        ];

        $this->warn("⚠️ Customer Data To Create on EMS ". json_encode($data));

        $response = Http::post($url, $data);

        $this->warn("⚠️ Save Customer Response ". json_encode($response->json()));

        // Log the raw response for review
        Log::info('EMS Account Creation Response', [
            'account_no' => $accountNo,
            'response' => $response->json(),
            'status' => $response->status()
        ]);

        // Display payload + response info in the console
        $this->info("🎉 EMS Account Creation Response for {$accountNo}:");
        $this->info("Payload: " . json_encode($data));
        $this->info("Response: " . json_encode($response->json()));
        $this->info("Status: " . $response->status());

        // Check if account number is already assigned to another customer
        $responseData = $response->json();

        if (isset($responseData['code']) && $responseData['code'] == '-1' && str_contains($responseData['desc'], 'already assigned')) {

            $this->warn("⚠️ Account number {$accountNo} already assigned — generating a new one...");

            // You can fetch feeder info if not already available
            $feeder = DSS::where("Assetid", $uploadHouses->dss)->first();

            $generateAccount = $this->createNewAccount($servicecode, $uploadHouses->dss, $feeder);

            if (!isset($generateAccount['error']) && isset($generateAccount['accountNumber'])) {
                $newAccountNo = $generateAccount['accountNumber'];

                $this->info("✅ New account number generated: {$newAccountNo}");

                // Update account record in DB
                $accountModel = PendingAccountCreation::find($id);
                if ($accountModel) {
                    $accountModel->update([
                        'account_no' => $newAccountNo
                    ]);
                    Log::info("PendingAccountCreation updated with new account number", [
                        'id' => $id,
                        'new_account_no' => $newAccountNo
                    ]);
                }

                // Retry EMS account creation with new number
                $this->info("🔁 Retrying EMS account creation with new account number: {$newAccountNo}");
                return $this->createEMSAccount($newAccountNo, $account, $uploadHouses, $servicecode, $dss, $id);
            } else {
               $this->error("❌ Failed to generate new account number. Response: " . json_encode($generateAccount));
                return false;
            }
        }

        return $response->successful();

    }


     /**
     * Finalize Account creation after EMS API success
     */
    private function finalizeAccount($account, $uploadHouses, $newAccountNo, $servicecode, $user)
    {

        
        // Convert JSON-decoded objects to actual models
        $accountModel = AccoutCreaction::find($account->id);
        $uploadHouseModel = UploadHouses::where(["id" => $uploadHouses->id, "status" => 6])->first();

         $this->info("🔁 Account Creation Model: {$accountModel}");
          $this->info("🔁 Upload Houses: {$uploadHouseModel}");


        if (!$accountModel || !$uploadHouseModel) {
            Log::error('❌ Missing related model during finalizeAccount', [
                'account_id' => $account->id ?? null,
                'upload_house_id' => $uploadHouses->id ?? null,
            ]);
            return;
        }


        // Update account record
        $existingAccountNos = $accountModel->account_no;
        $updatedAccountNo = $existingAccountNos
            ? $existingAccountNos . ',' . $newAccountNo
            : $newAccountNo;

        $accountModel->update([
            'status' => 'completed',
            'account_no' => $newAccountNo
        ]);

        // Update upload houses record
        $uploadHouseModel->update([
            'account_no' => $newAccountNo,
            'status' => 4
        ]);

        IbedcPayLogService::create([
            'module'     => 'New Account - Billing Approved',
            'comment'    => 'Account Successfully Created ' . $newAccountNo  . " by ". $user,
            'type'       => 'Approved',
            'module_id'  => $uploadHouseModel->id,
            'status'     => 'Completed',
        ]);

        // Since this runs in console, Auth::user() is not available, so we fake it:
        //$user = 'system@ibedc.com';

        dispatch(new CustomerAccountJob($uploadHouseModel, $account, $user));

        //Send SMS to Customer
        $baseUrl = env('SMS_MESSAGE');
        $idata = [
            'token' => env('SMS_TOKEN2'),
            'sender' => "IBEDC",
            'to' => $account->phone,
            "message" => "Dear Customer, your new IBEDC account number is $newAccountNo. For support, call 07001239999.",
            "type" => 0,
            "routing" => 3,
        ];

        $this->info('***** SMS SUCCESSFULLY SENT :: SMS has been sent to the customer *************');
        \Log::info("SMS SENT SUCCESSFULLY: ".   json_encode($idata));
        // /$iresponse = Http::asForm()->post($baseUrl, $idata);

          //Notify MSMS
       if($uploadHouses->paid_for_meter == "Yes" && !empty($newAccountNo)){ 
            $this->info("🔔 Notifying MSMS for Paid Meter... {$uploadHouses->paid_for_meter}");
            $msmsResponse = $this->notifyMSMS($uploadHouses, $newAccountNo);
            $this->info("🔔 MSMS Notification Response: " . json_encode($msmsResponse));
        } else {
            $this->info("🔔 Skipping MSMS notification — not a paid meter.  $uploadHouses->paid_for_meter   ||  account no: {$newAccountNo}");
         }

    }

    /**
     * Generate a new EMS account number
     */
    private function createNewAccount($servicecode, $dss, $feeder)
    {
        // Live API endpoint
        $url = "http://192.168.15.157:9549/AccountGenerator/webresources/account/generate/114/FX321G9D";

        $payload = [
            'utid'     => $servicecode->AREA_CODE ?? '',
            'buid'     => $servicecode->BUID ?? '',
            'dssid'    => $dss ?? '',
            'assetId'  => $feeder->Feeder_ID ?? ''
        ];

        // Log payload for visibility
        Log::info('EMS Account Number Generation Payload', $payload);
        $this->info("🆕 Generating new account number with payload: " . json_encode($payload));

        $response = Http::post($url, $payload);

        // Log raw response
        Log::info('EMS Account Number Generation Response', [
            'response' => $response->json(),
            'status' => $response->status()
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $this->info("✅ New account number generated successfully: " . json_encode($data));
            return $data;
        }

        // If request failed, show details
        $error = [
            'error' => true,
            'status' => $response->status(),
            'message' => $response->json()['error'] ?? $response->body()
        ];

        $this->error("❌ Failed to generate new account number: " . json_encode($error));
        return $error;
    }



    private function notifyMSMS($uploadHouses, $newAccountNo)
    {

        $this->info("📤 MSMS Upload Houses Notification Payload: " . json_encode($uploadHouses));
        // API Key
        $apiKey = "LIVEKEY_0XJLDYJZOQWF8UQ9XWVTH";

        $data = [
            "tracking_id" => $uploadHouses->id,
            "map_id" => $uploadHouses->map_id,
            "account_no" => $newAccountNo,    //$uploadHouses->account_no,
        ];

         // 🔍 Debug before sending
        $this->info("📤 MSMS Payload: " . json_encode($data));

         // Validate before sending
    if (empty($data['tracking_id']) || empty($data['map_id']) || empty($data['account_no'])) {
        $this->error("❌ MSMS payload has empty values");
        return ['error' => 'Invalid payload', 'data' => $data];
    }

         $createResponse = Http::withHeaders([
        'Authorization' => 'Bearer ' . $apiKey,
        'Accept' => 'application/json'
        ])->post(
            'https://msms.ibedc.com/api/v2/setup/update',
            $data
        );

        $this->info("📥 MSMS Response: " . $createResponse->body());

        return $createResponse->json();

    }





}

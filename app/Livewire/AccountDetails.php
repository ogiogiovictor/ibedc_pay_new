<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\NAC\AccoutCreaction;
use App\Models\NAC\UploadAccountCreation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Enums\RoleEnum;
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
use App\Models\NAC\PendingAccountCreation;


class AccountDetails extends Component
{

    public $details;

    public $showRejectModal = false;
    public $rejectComment;
    public $selectedDetailsId;
    public $selectedAccountId;

    public function mount($tracking_id)
    {
         $customers = new AccoutCreaction();

         $this->details = $customers
            ->with(['continuation', 'uploadinformation', 'caccounts', 'uploadedPictures'])
            ->where('tracking_id', $tracking_id)
            ->first();
    }


    public function processforbhm($id){

       
        $account = AccoutCreaction::find($id);
      

        if (!$account) {
            Session::flash('error', 'Account not found.');
            return;
        }

        // Get all UploadHouses with the same tracking_id
        $uploadHouses = UploadHouses::where("tracking_id", $account->tracking_id)->get();

        // Check if all have status == 1
        $allWithDtm = $uploadHouses->every(function ($house) {
            return $house->status == 1;
        });

        if ($allWithDtm) {
            // Update status of the account
            $account->update([
                'status' => 'with-bhm',
                'status_name' => 'Application passed validation'
            ]);

            Session::flash('success', 'Customer Successfully Mapped.');
        } else {
            Session::flash('error', 'Some accounts/records is still pending for this account');
        }
       
    }


     public function approveforbilling($id){


        $account = AccoutCreaction::find($id);
      

        if (!$account) {
            Session::flash('error', 'Account not found.');
            return;
        }

        if ($account->status == 'with-bhm') {
            // Update status of the account
            $account->update([
                'status' => 'with-billing',
                'status_name' => 'Application in final stage'
            ]);

          UploadHouses::where("customer_id", $id)->update([
                'status' => 4,
                'approved_by' => Auth::user()->id
            ]);

            Session::flash('success', 'Customer Request Successfully Approved.');
        } else {
            Session::flash('error', 'Eror Approving Request, Please try again later');
        }
       
    }



    public function rejectbacktodtm($id) {
       
        $account = AccoutCreaction::find($id);

        if (!$account) {
            Session::flash('error', 'Account not found.');
            return;
        }

        if ($account->status == 'with-bhm') {
            // Update status of the account
            $account->update([
                'status' => 'started',
                'status_name' => 'rejected'
            ]);

        $uploadHouses = UploadHouses::where("tracking_id", $account->tracking_id)->get();
        $uploadHouses->update([
                'status' => 0,
                'status_name' => 'Application Request Rejected'
            ]);

            Session::flash('success', 'Customer Successfully Rejected.');
        } 

    }

    // public function billingreject($aid, $uid){


    //      $account = AccoutCreaction::find($aid);

    //     if (!$account) {
    //         Session::flash('error', 'Account not found.');
    //         return;
    //     }

    //      $uploadHouses = UploadHouses::where("id", $uid)->first();

    //     if ($uploadHouses->status == '2') {
    //         // Update status of the account
    //         $account->update([
    //             'status' => 'started',
    //             'status_name' => 'rejected',
    //             'comment' => 'Your account was rejected'
    //         ]);

           
    //         $uploadHouses->update([
    //                 'status' => 1,
    //                 'billing_comment' => 'Application Request Rejected By Billing',
    //                 'lecan_link' => NULL
    //             ]);

    //          $email = $uploadHouses->validated_by;

    //         $name = Auth::user()->name;

    //           IbedcPayLogService::create([
    //                 'module'     => 'New Account - Billing Reject',
    //                 'comment'    => 'Request Reject By Billing ', $name,
    //                 'type'       => 'Rejected',
    //                 'module_id'  => $uploadHouses->id,
    //                 'status'     => 'Rejected',
    //             ]);

    //        if (!empty($email)) {
    //         // Send email with token
    //             Mail::raw("Your request with tracking ID was rejected. Tracking ID is: {$uploadHouses->tracking_id} Comments: Your account was rejected ", function ($message) use ($email) {
    //                 $message->to($uploadHouses->validated_by)
    //                         ->subject('New Account Request Rejected');
    //             });
    //         }

    //         Session::flash('success', 'New Account Successfully Rejected By .');
    //     } 

    //      Session::flash('error', 'Error Rejecting Request.');
    // }




    public function generate($id, $aid)
    {

        $account = AccoutCreaction::find($id);
        $uploadHouses = UploadHouses::find($aid);

    
      //  return $uploadHouses;

        // if (!$account || $account->status !== 'with-billing' || $account->account_no) {
        //     return $this->flashError('The request has already been processed: ' . $account->account_no);
        // }

        if ($uploadHouses->status == '4' || $uploadHouses->account_no) {
            return $this->flashError('The request has already been processed: ' . $account->account_no);
        }

        if(!$uploadHouses) {
             return $this->flashError('No Account Result for this customer: ' . $uploadHouses->dss);
        }


        $buid = BusinessUnit::where("Name", strtoupper($uploadHouses->business_hub))->first();
        $servicecode = $this->getAvailableServiceCode($uploadHouses);

        if (!$servicecode) {
            return $this->flashError('All book numbers in the service center are exhausted or No Service Center With That Name. SERVICE CENTER: ' . $uploadHouses->service_center);
        }

       
        

        

        $udertaking = Undertaking::where("buid", $buid->BUID)->first();
        $feeder = DSS::where("Assetid", $uploadHouses->dss)->first();

        if (!$feeder || !$udertaking) {
            return $this->flashError('Missing DSS or Undertaking. Please contact IT.');
        }

         // $servicecode->AREA_CODE ?? ltrim($udertaking->UTID

         $unsedAccount = $this->getUnusedAccount($servicecode, $udertaking);

      //   dd($unsedAccount);
        //  dd( $servicecode->AREA_CODE, $buid->BUID);
          

       //   dd($uploadHouses->dss, $feeder);

          if (isset($unsedAccount['error'])) {
            $generateAccount = $this->createNewAccount($servicecode, $uploadHouses->dss, $feeder);

           // dd($generateAccount);
             //dd($generateAccount['message']);

           // if (!$generateAccount || $generateAccount['error'] == true) {
            if (isset($generateAccount['error']) && $generateAccount['error'] == true) {

                return $this->flashError($generateAccount['message']);
            }
        } else {
            $generateAccount = ['accountNumber' => $unsedAccount['accountNumbers'][0]];
        }

        //  dd($generateAccount);

         if (!$this->createEMSAccount($generateAccount['accountNumber'], $account, $uploadHouses, $servicecode, $uploadHouses->dss)) {
            return $this->flashError('Failed to create EMS account.');
        }

        $this->finalizeAccount($account, $uploadHouses, $generateAccount['accountNumber'], $servicecode);

        Session::flash('success', 'Customer Successfully Generated.');

    }



    private function getAvailableServiceCode($uploadHouses)
    {
       
        return ServiceAreaCode::whereRaw("LOWER(TRIM(Service_Centre)) = ?", [strtolower(trim($uploadHouses->service_center))])
        ->whereRaw("LOWER(TRIM(BHUB)) = ?", [strtolower(trim($uploadHouses->business_hub))])
        ->where('number_of_customers', '<=', 1000)
        ->first();
        // return ServiceAreaCode::where('Service_Centre', $uploadHouses->service_center)
        //     ->where('BHUB', $uploadHouses->business_hub)
        //     ->where('number_of_customers', '<=', 1000)
        //     ->first();
    }


     private function getUnusedAccount($servicecode, $udertaking)
    {
       // $url = "http://192.168.15.17:8080/AccountGenerator/webresources/account/unused/114/FX321G9D";  // test api
        $url = "http://192.168.15.157:9494/AccountGenerator/webresources/account/unused/114/FX321G9D";  // live api
        $payload = [
            'utid' => $servicecode->AREA_CODE ?? ltrim($udertaking->UTID, '/'),   // "35/52", //  
            'buid' => $servicecode->BUID   // "35A", // 
        ];

        return Http::post($url, $payload)->json();
    }


     private function createNewAccount($servicecode, $dss, $feeder)
    { 
       // $url = "http://emsecmitest:8080/AccountGenerator/webresources/account/generate/114/FX321G9D";  // test api
        $url = "http://192.168.15.157:9494/AccountGenerator/webresources/account/generate/114/FX321G9D";  // live api
        $payload = [
            'utid' => $servicecode->AREA_CODE,
            'buid' => $servicecode->BUID,
            'dssid' => $dss,
            'assetId' => $feeder->Feeder_ID
        ];

        $response = Http::post($url, $payload);
        if ($response->successful()) {
             return $response->json();
        }

        // Return a structured error response
        return [
            'error' => true,
            'status' => $response->status(),
            'message' => $response->json()['error'] ?? $response->body()
        ];
        //return $response->successful() ? $response->json() : null;
    }


    public function approveAccount($id, $aid) {

        $account = AccoutCreaction::find($id);
        $uploadHouses = UploadHouses::find($aid);
        $landlordInfo = ContinueAccountCreation::where("tracking_id", $uploadHouses->tracking_id)->first();
        $servicecode = $this->getAvailableServiceCode($uploadHouses);
        
         $url = "http://192.168.15.157:9494/AccountGenerator/webresources/account/save/customer/114/FX321G9D";  // live api

        $data = [
            "accountNo" => $account->account_no,
            "meterNo" => "",
            "surname" => $landlordInfo->landlord_surname,
            "firstName" => $landlordInfo->landlord_othernames,
            "otherNames" => "", // $account->other_name,
            "email" => $landlordInfo->email ?: $account->email,
            "serviceAddress1" => $uploadHouses->house_no . ' ' . $uploadHouses->full_address,
            "serviceAddress2" => $uploadHouses->business_hub,
            "serviceAddressCity" => $uploadHouses->service_center,
            "serviceAddressState" => $uploadHouses->region,
            "tariffID" => $uploadHouses->tarrif,
            "arrears" => '',
            "mobile" => $landlordInfo->telephone ?: $account->phone,
            "gisCoordinate" => $uploadHouses->latitude . ',' . $uploadHouses->longitude,
            "buid" => $servicecode->BUID, //"35A"
            "distributionID" => $uploadHouses->dss,
            "accessGroup" => "Administrator"
        ];

        $this->finalizeAccount($account, $uploadHouses, $account->account_no, $servicecode);

        $response = Http::post($url, $data);
        return $response->successful();

    }

    private function createEMSAccount($accountNo, $account, $uploadHouses, $servicecode, $dss)
    {
       // $url = "http://emsecmitest:8080/AccountGenerator/webresources/account/save/customer/114/FX321G9D";  // test api
        $url = "http://192.168.15.157:9494/AccountGenerator/webresources/account/save/customer/114/FX321G9D";  // live api

        $landlordInfo = ContinueAccountCreation::where("tracking_id", $uploadHouses->tracking_id)->first();

        $data = [
            "accountNo" => $accountNo,
            "meterNo" => "",
            "surname" => $landlordInfo->landlord_surname, // $account->surname,
            "firstName" => $landlordInfo->landlord_othernames, // $account->firstname,
            "otherNames" => "", //$uploadHouses->tracking_id,   //$account->other_name,
            "email" => $account->email,
            "serviceAddress1" => $uploadHouses->house_no . ' ' . $uploadHouses->full_address,
            "serviceAddress2" => $uploadHouses->business_hub,
            "serviceAddressCity" => $uploadHouses->service_center,
            "serviceAddressState" => $uploadHouses->region,
            "tariffID" => $uploadHouses->tarrif,
            "arrears" => '',
            "mobile" =>  $landlordInfo->landlord_telephone  ?? $account->phone,
            "gisCoordinate" => $uploadHouses->latitude . ',' . $uploadHouses->longitude,
            "buid" => $servicecode->BUID, //"35A"
            "distributionID" => $dss,
            "accessGroup" => "Administrator"
        ];

      //dd($data);

        $response = Http::post($url, $data);
        return $response->successful();

       // dd($response->json());
        
    }


    private function finalizeAccount($account, $uploadHouses, $newAccountNo, $servicecode)
    {

        $existingAccountNos = $account->account_no;
        $updatedAccountNo = $existingAccountNos ? $existingAccountNos . ',' . $newAccountNo : $newAccountNo;

        $account->update([
            'status' => 'completed',
            'account_no' => $updatedAccountNo
        ]);

        $uploadHouses->update([
            'account_no' => $newAccountNo,
            'status' => 4
        ]);


        IbedcPayLogService::create([
                    'module'     => 'New Account - Billing Approved',
                    'comment'    => 'Account Successfully Created '. $newAccountNo,
                    'type'       => 'Approved',
                    'module_id'  => $uploadHouses->id,
                    'status'     => 'Completed',
         ]);

         
        $user = Auth::user()->email;

        dispatch(new CustomerAccountJob($uploadHouses, $account, $user));

        //  if ($servicecode) {
        //     $servicecode->increment('number_of_customers');
        // }

    }

    private function flashError($message)
    {
       // Session::flash('error', $message);
        Session::put('error', $message);
        return;
    }


    public function ricoApprove($id, $aid) {

        $account = AccoutCreaction::find($id);
        $uploadHouses = UploadHouses::find($aid);

        $name = Auth::user()->name;
   
         $uploadHouses->update([
            'status' => 2,
        ]);

        IbedcPayLogService::create([
                'module'     => 'New Account',
                'comment'    => "Approved By ". $name,
                'type'       => 'Approved',
                'module_id'  => $aid,
                'status'     => 'with-billing',
        ]);

         Session::flash('success', 'Account Successfully Generated.');

    }


    public function confirmBillingReject($detailsId, $accountId)
        {
            $this->selectedDetailsId = $detailsId;
            $this->selectedAccountId = $accountId;
            $this->rejectComment = '';
            $this->showRejectModal = true;
        }

    public function submitBillingReject()
    {
        $this->validate([
            'rejectComment' => 'required|string|max:500',
        ]);

        $this->billingreject($this->selectedDetailsId, $this->selectedAccountId, $this->rejectComment);
        $this->showRejectModal = false;
        $this->reset(['rejectComment', 'selectedDetailsId', 'selectedAccountId']);
    }


    public function billingreject($aid, $uid, $comment = null)
    {
        $account = AccoutCreaction::find($aid);

        if (!$account) {
            session()->flash('error', 'Account not found.');
            return;
        }

        $uploadHouses = UploadHouses::where("id", $uid)->first();

        if ($uploadHouses->status == '2') {
            $account->update([
                'status' => 'started',
                'status_name' => 'rejected',
                'comment' => $comment ?? 'Your account was rejected'
            ]);

            $uploadHouses->update([
                'status' => 1,
                'billing_comment' => $comment ?? 'Application Request Rejected By Billing',
                'lecan_link' => null
            ]);

            $email = $uploadHouses->validated_by;
            $name = Auth::user()->name;

            IbedcPayLogService::create([
                'module' => 'New Account - Billing Reject',
                'comment' => $comment .  " - Request Rejected By Billing ({$name})",
                'type' => 'Rejected',
                'module_id' => $uploadHouses->id,
                'status' => 'Rejected',
            ]);

            if (!empty($email)) {
                Mail::raw("Your request with tracking ID {$uploadHouses->tracking_id} was rejected.\nComments: {$comment}", function ($message) use ($email) {
                    $message->to($email)->subject('New Account Request Rejected');
                });
            }

            session()->flash('success', 'New Account successfully rejected.');
            return;
        }

        session()->flash('error', 'Error rejecting request.');
    }





    public function stageRequest($id, $aid) {

        $account = AccoutCreaction::find($id);
        $uploadHouses = UploadHouses::find($aid);

         if ($uploadHouses->status == '4' || $uploadHouses->account_no) {
            return $this->flashError('The request has already been processed: ' . $account->account_no);
        }

        if(!$uploadHouses) {
             return $this->flashError('No Account Result for this customer: ' . $uploadHouses->dss);
        }


        $buid = BusinessUnit::where("Name", strtoupper($uploadHouses->business_hub))->first();
        $servicecode = $this->getAvailableServiceCode($uploadHouses);

        if (!$servicecode) {
            return $this->flashError('All book numbers in the service center are exhausted or No Service Center With That Name. SERVICE CENTER: ' . $uploadHouses->service_center);
        }

         $udertaking = Undertaking::where("buid", $buid->BUID)->first();
        $feeder = DSS::where("Assetid", $uploadHouses->dss)->first();

        if (!$feeder || !$udertaking) {
            return $this->flashError('Missing DSS or Undertaking. Please contact IT.');
        }


         $generateAccount = $this->createNewAccount($servicecode, $uploadHouses->dss, $feeder);
 
         if (!isset($generateAccount['error']) && isset($generateAccount['accountNumber'])) {
             
              $newAccountNo = $generateAccount['accountNumber'];

              $this->creatingStaging($newAccountNo, $account, $uploadHouses, $servicecode, $uploadHouses->dss);
       
             Session::flash('success', 'Customer Successfully Generated. But Pending On EMS');
         }

            Session::flash('error', 'Error staging customer information');

    }



    private function creatingStaging($accountnumber, $account, $uploadhouses, $servicecode, $dss) {

         $user = Auth::user();

        $create = PendingAccountCreation::create([
            'account_no' => $accountnumber,
            'account' => json_encode($account),
            'upload_houses' => json_encode($uploadhouses),
            'upload_houses_id' => $uploadhouses->id,
            'service_code' => $servicecode,
            'dss' => $dss,
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user' => json_encode([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]),
            'tracking_id' =>  $uploadhouses->tracking_id
        ]);

        if($create) {

            $uploadhouses->update([
                'status' => 6   // This stage is for job waiting for account to be created.
            ]);

             Session::flash('success', 'Customer Successfully Successfully Stagged for account creation. Assigned Account number is {{accountnumber}}');
        } else {
             Session::flash('error', 'Error staging customer information');
        }

    }



 

    public function render()
    {
        return view('livewire.account-details');
    }
}

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


class AccountDetails extends Component
{

    public $details;

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

    public function billingreject($aid, $uid){


         $account = AccoutCreaction::find($aid);

        // dd($account);

        if (!$account) {
            Session::flash('error', 'Account not found.');
            return;
        }

        if ($account->status == 'with-billing') {
            // Update status of the account
            $account->update([
                'status' => 'started',
                'status_name' => 'rejected',
                'comment' => 'Your account was rejected'
            ]);

        $uploadHouses = UploadHouses::where("id", $uid)->first();
        $uploadHouses->update([
                'status' => 1,
                'billing_comment' => 'Application Request Rejected By Billing',
               // 'lecan_link' => NULL
            ]);

          $email = $uploadHouses->validated_by;

           if (!empty($email)) {
            // Send email with token
                Mail::raw("Your request with tracking ID was rejected. Tracking ID is: {$uploadHouses->tracking_id} Comments: Your account was rejected ", function ($message) use ($email) {
                    $message->to($uploadHouses->validated_by)
                            ->subject('New Account Request Rejected');
                });
            }

            Session::flash('success', 'New Account Successfully Rejected By .');
        } 
    }




    public function generate($id, $aid)
    {

        $account = AccoutCreaction::find($id);
        $uploadHouses = UploadHouses::find($aid);

      //  return $uploadHouses;

        // if (!$account || $account->status !== 'with-billing' || $account->account_no) {
        //     return $this->flashError('The request has already been processed: ' . $account->account_no);
        // }

        if ($account->status == '4' || $account->account_no) {
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
            "mobile" => $account->phone,
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

        dispatch(new CustomerAccountJob($uploadHouses, $account));

        //  if ($servicecode) {
        //     $servicecode->increment('number_of_customers');
        // }

    }

      private function flashError($message)
    {
       Session::flash('error', $message);
        //Session::put('error', $message);
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













    // public function generateAccount($id, $aid){

    //    //dd($id, $aid);

    //    // get the region and the business hubs
    //         $account = AccoutCreaction::find($id);

    //         //dd($account);

    //         if( $account->status != "with-billing" || $account->account_no != '') {
    //                 Session::flash('error', "The request has already been processed". $account->account_no);
    //                 return;
    //             }
        
    //         // Get all single account
    //         $uploadHouses = UploadHouses::where("id", $aid)->first();

    //         // get the buid
    //         $buid = BusinessUnit::where("Name", strtoupper($uploadHouses->business_hub))->first();

    //         $servicecode = ServiceAreaCode::where('Service_Centre', $uploadHouses->service_center)
    //             ->where('BHUB', $uploadHouses->business_hub)
    //             ->where('number_of_customers', '<=', 1000)
    //             ->first();

    //             if(!$servicecode) {
    //                 Session::flash('error', 'All the book numbers in the service center is exhausted. SERVICE CENTER:-'. $uploadHouses->service_center);
    //                 return;
    //             }

            
    //         // get the undertaken
    //         $udertaking = Undertaking::where("buid", $buid->BUID)->first();

    //         // get the dss
    //         $dss = $uploadHouses->dss;

        
    //         $feeder = DSS::where("Assetid",  $dss)->first();

    //         if (!$account) {
    //                 Session::flash('error', 'Account not found.');
    //                 return;
    //             }

            
    //             if(!$feeder){
    //                 Session::flash('error', 'Account cannot be generated, No feeder tied to their DSS, Please Contact IT');
    //                 return;
    //         }

    //         if(!$udertaking && !$dss){
    //                 Session::flash('error', 'No DSS or Undertaken for this Customer, please contact IT');
    //                 return;
    //         }

    //             $unsedLinkURL = "http://192.168.15.17:8080/AccountGenerator/webresources/account/unused/114/FX321G9D";

    //             $flutterData = [
    //                 'utid' => $servicecode->AREA_CODE ?? ltrim($udertaking->UTID, '/'),
    //                 "buid" => $servicecode->BUID ?? $buid->BUID
    //             ];

    //             // Check for unsed account
    //             $iresponse = Http::post($unsedLinkURL, $flutterData);
    //             $unsedAccount = $iresponse->json(); 

                
    //             $createAccountLinkURL = "http://emsecmitest:8080/AccountGenerator/webresources/account/generate/114/FX321G9D";



    //         /////////////////////////////////////////////// GENERATE ACCOUNT FOR UNSED ACCOUNT //////////////////////////////////////////
    //             if($unsedAccount['error']) {
                
    //                 $createAccountData = [
    //                     'utid' => $servicecode->AREA_CODE, // ltrim($udertaking->UTID, '/'),
    //                     "buid" =>  $servicecode->BUID,
    //                     "dssid" => $dss,
    //                     "assetId" => $feeder->Feeder_ID
    //                 ];

    //                 if(strlen(ltrim($udertaking->UTID, '/')) !== 5) {
    //                     Session::flash('error', "Invalid UTID, Please contact billing / IT UTID". $udertaking->UTID);
    //                     return;
    //                 }

    //                 // Check for unsed account
    //                 $iresponse = Http::post($createAccountLinkURL, $createAccountData);
    //                 $generateAccount = $iresponse->json();
                    
    //                 if($generateAccount['error']) {
    //                     Session::flash('error', $unsedAccount['error']);
    //                     return;
    //                 }

    //                 // Check if the request was successful
    //                 if (!$iresponse->successful()) {
    //                     Session::flash('error', 'Failed to create account. Server returned an error.');
    //                     return;
    //                 }


    //                 if ($account->status == 'with-billing') {


    //                 // before we update the account do something now create the account in EMS database.
                    
    //                 $createonEMSLink = "http://emsecmitest:8080/AccountGenerator/webresources/account/save/customer/114/FX321G9D";
    //                 //http://emsecmitest:8080/AccountGenerator/webresources/account/save/customer/{MerchantID}/{AccessToken}

    //                 $accountDatatoCreate = [
    //                     "accountNo" =>  $generateAccount['accountNumber'],
    //                     "meterNo" => "",
    //                     "surname" =>  $account->surname,
    //                     "firstName" => $account->firstname,
    //                     "otherNames" => $account->other_name,
    //                     "email" => $account->email,
    //                     "serviceAddress1" => $uploadHouses->house_no. ' '.  $uploadHouses->full_address,
    //                     "serviceAddress2"=> $uploadHouses->business_hub,
    //                     "serviceAddressCity" => $uploadHouses->service_center,
    //                     "serviceAddressState" => $uploadHouses->business_hub,
    //                     "tariffID" =>   $uploadHouses->tarrif_id,
    //                     "arrears" => '',
    //                     "mobile" =>  $account->phone,
    //                     "gisCoordinate" => $uploadHouses->latitude. ','. $uploadHouses->longitude,
    //                     "buid" => $servicecode->BUID,
    //                     "distributionID" => $dss,
    //                     "accessGroup" => "Administrator"
    //                 ];


    //                 $newresponse = Http::post($createonEMSLink, $accountDatatoCreate);
    //                 $generateAccount = $iresponse->json();


    //                 // Update status of the account
    //                 $newAccountNo = $generateAccount['accountNumber']; // or whatever the new value is
    //                 $existingAccountNos = $account->account_no;

    //                 // Append with comma if not empty
    //                 $updatedAccountNo = $existingAccountNos  ? $existingAccountNos . ',' . $newAccountNo  : $newAccountNo;

                    
    //                 $account->update([
    //                     'status' => 'completed',
    //                     'account_no' =>  $updatedAccountNo 
    //                 ]);

    //                 $uploadHouses->update([
    //                     'account_no' => $generateAccount['accountNumber'],
    //                     'status' => 2
    //                 ]);

    //                 if ($servicecode) {
    //                     $servicecode->increment('number_of_customers');
    //                 }

    //                 // Send the customer an email informating the customer 
    //                 dispatch(new CustomerAccountJob($uploadHouses, $account));


    //                 Session::flash('success', 'Customer Successfully Generated.');
                    
    //             } else {
    //                         Session::flash('error', 'Some accounts/records is still pending for this account');
                        
    //                     }

    //             } else {

    //                 // Pick the first account no
    //             $accountNo =  $unsedAccount['accountNumbers'][0];

    //                 $createonEMSLink = "http://emsecmitest:8080/AccountGenerator/webresources/account/save/customer/114/FX321G9D";
    //                 //http://emsecmitest:8080/AccountGenerator/webresources/account/save/customer/{MerchantID}/{AccessToken}

    //                 $accountDatatoCreate = [
    //                     "accountNo" =>  $generateAccount['accountNumber'],
    //                     "meterNo" => "",
    //                     "surname" =>  $account->surname,
    //                     "firstName" => $account->firstname,
    //                     "otherNames" => $account->other_name,
    //                     "email" => $account->email,
    //                     "serviceAddress1" => $uploadHouses->house_no. ' '.  $uploadHouses->full_address,
    //                     "serviceAddress2"=> $uploadHouses->business_hub,
    //                     "serviceAddressCity" => $uploadHouses->service_center,
    //                     "serviceAddressState" => $uploadHouses->business_hub,
    //                     "tariffID" =>   $uploadHouses->tarrif_id,
    //                     "arrears" => '',
    //                     "mobile" =>  $account->phone,
    //                     "gisCoordinate" => $uploadHouses->latitude. ','. $uploadHouses->longitude,
    //                     "buid" => $servicecode->BUID,
    //                     "distributionID" => $dss,
    //                     "accessGroup" => "Administrator"
    //                 ];


    //                 $newresponse = Http::post($createonEMSLink, $accountDatatoCreate);
    //                 $generateAccount = $iresponse->json();

    //                 // Update status of the account
    //                 $newAccountNo = $generateAccount['accountNumber']; // or whatever the new value is
    //                 $existingAccountNos = $account->account_no;

    //                 // Append with comma if not empty
    //                 $updatedAccountNo = $existingAccountNos  ? $existingAccountNos . ',' . $newAccountNo  : $newAccountNo;

                    
    //                 $account->update([
    //                     'status' => 'completed',
    //                     'account_no' =>  $updatedAccountNo 
    //                 ]);

    //                 $uploadHouses->update([
    //                     'account_no' => $generateAccount['accountNumber'],
    //                     'status' => 2
    //                 ]);

    //                 if ($servicecode) {
    //                     $servicecode->increment('number_of_customers');
    //                 }

    //                 // Send the customer an email informating the customer 
    //                 dispatch(new CustomerAccountJob($uploadHouses, $account));


    //                 Session::flash('success', 'Customer Successfully Generated.');
                    
    //             // Session::flash('error', 'There are some account that is pending that you need to use.');
    //             // return;
    //             }
            
       
    // }


    public function render()
    {
        return view('livewire.account-details');
    }
}

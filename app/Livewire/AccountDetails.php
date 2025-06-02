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
use Illuminate\Support\Facades\Http;

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
                'status' => 'with-bhm'
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
                'status' => 'with-billing'
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
                'status' => 'processing'
            ]);

            Session::flash('success', 'Customer Successfully Rejected.');
        } 

    }



    public function generateAccount($id, $aid){

       //dd($id, $aid);

       // get the region and the business hubs
       $account = AccoutCreaction::find($id);

       if( $account->status == 2 || $account->account_no != '') {
            Session::flash('error', "The request has already been processed". $account->account_no);
            return;
        }
 

      // Get all single account
       $uploadHouses = UploadHouses::where("id", $aid)->first();

      // get the buid
       $buid = BusinessUnit::where("Name", strtoupper($uploadHouses->business_hub))->first();

       
       // get the undertaken
       $udertaking = Undertaking::where("buid", $buid->BUID)->first();

       // get the dss
       $dss = $uploadHouses->dss;

   
       $feeder = DSS::where("Assetid",  $dss)->first();

       if (!$account) {
            Session::flash('error', 'Account not found.');
            return;
        }

     
        if(!$feeder){
            Session::flash('error', 'Account cannot be generated, No feeder tied to their DSS, Please Contact IT');
            return;
       }

       if(!$udertaking && !$dss){
            Session::flash('error', 'No DSS or Undertaken for this Customer, please contact IT');
            return;
       }

        $unsedLinkURL = "http://192.168.15.17:8080/AccountGenerator/webresources/account/unused/114/FX321G9D";

        $flutterData = [
            'utid' => ltrim($udertaking->UTID, '/'),
            "buid" => $buid->BUID
        ];

        // Check for unsed account
        $iresponse = Http::post($unsedLinkURL, $flutterData);
        $unsedAccount = $iresponse->json(); 

        if($unsedAccount['error']) {
            Session::flash('error', $unsedAccount['error']);
            return;
        }


        $createAccountLinkURL = "http://emsecmitest:8080/AccountGenerator/webresources/account/generate/114/FX321G9D";

        $createAccountData = [
            'utid' => "21/10", // ltrim($udertaking->UTID, '/'),
            "buid" => $buid->BUID,
            "dssid" => $dss,
            "assetId" => $feeder->Feeder_ID
        ];

        if(strlen(ltrim($udertaking->UTID, '/')) !== 5) {
            Session::flash('error', "Invalid UTID, Please contact billing / IT UTID". $udertaking->UTID);
            return;
        }
 
         // Check for unsed account
        $iresponse = Http::post($createAccountLinkURL, $createAccountData);
        $generateAccount = $iresponse->json();
        
         if($generateAccount['error']) {
            Session::flash('error', $unsedAccount['error']);
            return;
        }

        if ($account->status == 'with-billing') {
            // Update status of the account

             $newAccountNo = $generateAccount['accountNumber']; // or whatever the new value is
             $existingAccountNos = $account->account_no;

             // Append with comma if not empty
            $updatedAccountNo = $existingAccountNos  ? $existingAccountNos . ',' . $newAccountNo  : $newAccountNo;

            
            $account->update([
                'status' => 'completed',
                'account_no' =>  $updatedAccountNo 
            ]);

             $uploadHouses->update([
                'account_no' => $generateAccount['accountNumber'],
                'status' => 2
            ]);

            // Send the customer an email informating the customer 


            Session::flash('success', 'Customer Successfully Generated.');
        } else {
            Session::flash('error', 'Some accounts/records is still pending for this account');
        }
       
    }


    public function render()
    {
        return view('livewire.account-details');
    }
}

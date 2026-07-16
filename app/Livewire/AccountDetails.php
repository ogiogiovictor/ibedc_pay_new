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

use App\Models\EMS\DistributionSubStation;
use App\Models\EMS\UndertakingNumber;
use App\Models\EMS\Feeder;
use App\Models\EMS\NewTarrif;



class AccountDetails extends Component
{

    public $details;
    public $trackingid;

    public $showRejectModal = false;
    public $rejectComment;
    public $selectedDetailsId;
    public $selectedAccountId;

    public $selected = [];        // store selected IDs
    public $selectAll = false;    // top checkbox toggle

    public function mount($tracking_id)
    {
         $customers = new AccoutCreaction();

         $this->details = $customers
            ->with(['continuation', 'uploadinformation', 'caccounts', 'uploadedPictures'])
            ->withCount('uploadedPictures')
            ->where('tracking_id', $tracking_id)
            ->first();
    }


    public function updatedSelectAll($value)
    {
        if ($value) {
            // Select only accounts with status = 2
            $this->selected = $this->details->uploadedPictures
                ->where('status', 2)
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selected = [];
        }

    }


    public function updatedSelected()
    {
        $totalSelectable = $this->details->uploadedPictures->where('status', 2)->count();

        $this->selectAll = count($this->selected) === $totalSelectable;
    }


    public function approveSelected()
    {
        if (empty($this->selected)) {
            session()->flash('error', 'No accounts selected.');
            return;
        }

        // Approve all in one shot
        UploadHouses::whereIn('id', $this->selected)
            ->update(['evaluated' => 'yes']); // move to billing or next stage

        session()->flash('success', 'Selected accounts approved successfully.');

        // Reset
        $this->selected = [];
        $this->selectAll = false;

        // Refresh
        $this->details->refresh();
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


        
        $businessHub = trim($uploadHouses->business_hub);  //strtoupper($uploadHouses->business_hub)
        $serviceCenter = trim($uploadHouses->service_center);

        // Normalize business hub value
        if (strcasecmp($serviceCenter, 'Ijebu-Ode') === 0) {
            $serviceCenter = 'Ijebu';
        }


         // Normalize business hub value
        if (strcasecmp($businessHub, 'Ijebu-Ode') === 0) {
            $businessHub = 'Ijebu';
        }

        if (strcasecmp($businessHub, 'Ilesa') === 0) {
            $businessHub = 'ILESHA';
        }

         if (strcasecmp($serviceCenter, 'Ijebu jesa') === 0) {
            $serviceCenter = 'IJEBU JESA';
        }


         if (strcasecmp($businessHub, 'Saapade') === 0) {
            $businessHub = 'Sapade';
        }

         // Normalize business hub value
        if (strcasecmp($serviceCenter, 'IJEBU IGBO') === 0) {
            $serviceCenter = 'IJEBU-IGBO';
        }

          // Normalize business hub value
        if (strcasecmp($serviceCenter, 'BODE-OLUDE') === 0) {
            $serviceCenter = 'BODE OLUDE';
        }


           // Normalize business hub value
        if (strcasecmp($serviceCenter, 'IDIROKO ROAD') === 0) {
            $serviceCenter = 'IDIROKO';
        }

        $buid = BusinessUnit::where("Name", strtoupper($businessHub))->first();
        $servicecode = $this->getAvailableServiceCode($uploadHouses);

       // dd($uploadHouses->business_hub);

        if (!$servicecode) {
            return $this->flashError('All book numbers in the service center are exhausted or No Service Center With That Name. SERVICE CENTER: ' . $uploadHouses->service_center);
        }

       
        $udertaking = Undertaking::where("buid", $buid->BUID)->first();
       // $feeder = DSS::where("Assetid", $uploadHouses->dss)->first();

        if (!$udertaking) {
            return $this->flashError('Undertaken not found. Please contact IT.');
        }

         // $servicecode->AREA_CODE ?? ltrim($udertaking->UTID

         $unsedAccount = $this->getUnusedAccount($servicecode, $udertaking);

        // dd($unsedAccount['accountNo'][0]);
        //  dd( $servicecode->AREA_CODE, $buid->BUID);
          

       //   dd($uploadHouses->dss, $feeder);

          if (isset($unsedAccount['error'])) {
            $generateAccount = $this->createNewAccount($servicecode, $uploadHouses->dss, $feeder);

             //dd($generateAccount);
             //dd($generateAccount['message']);

           // if (!$generateAccount || $generateAccount['error'] == true) {
            if (isset($generateAccount['error']) && $generateAccount['error'] == true) {

                return $this->flashError($generateAccount['message']);
            }
        } else {
            $generateAccount = ['accountNumber' => $unsedAccount['accountNo'][0]];
        }

        $checkIfExist = UploadHouses::where("account_no", $generateAccount['accountNumber'])->first();

         if ($checkIfExist) {
            return $this->flashError('Account Exist, please retry ' . $account->account_no);
        }

         if (!$this->createEMSAccount($generateAccount['accountNumber'], $account, $uploadHouses, $servicecode, $uploadHouses->dss)) {
            return $this->flashError('Failed to create EMS account.');
        }

        $this->finalizeAccount($account, $uploadHouses, $generateAccount['accountNumber'], $servicecode);

        Session::flash('success', 'Customer Successfully Generated.');

    }



    private function getAvailableServiceCode($uploadHouses)
    {

        $businessHub = trim($uploadHouses->business_hub);
        $serviceCenter = trim($uploadHouses->service_center);


       // dd($businessHub, $serviceCenter);

         // Normalize business hub value
        if (strcasecmp($businessHub, 'Ijebu-Ode') === 0) {
            $businessHub = 'Ijebu';
        }

        if (strcasecmp($businessHub, 'Ijebu Igbo') === 0) {
            $businessHub = 'Ijebu';
        }

         //This is the problem wiht oke-soda comment later
        // if (strcasecmp($businessHub, 'Ile-ife') === 0) {
        //     $businessHub = 'ILEIFE';
        // }

        if (strcasecmp($serviceCenter, 'Ijebu jesa') === 0) {
            $serviceCenter = 'IJEBU JESA';
        }

         if (strcasecmp($serviceCenter, 'Ero omo') === 0) {
            $serviceCenter = 'Ero-omo';
        }
        
         // Normalize business hub value
        if (strcasecmp($serviceCenter, 'AGO IWOYE') === 0) {
            $serviceCenter = 'AGO-IWOYE';
        }

        // Normalize business hub value
        if (strcasecmp($serviceCenter, 'ITA-OSHIN') === 0) {
            $serviceCenter = 'ITA OSHIN';
        }

        if (strcasecmp($serviceCenter, 'IMOWO II') === 0) {
            $serviceCenter = 'IMOWO 2 ';
        }

        if (strcasecmp($serviceCenter, 'ILORA') === 0) {
            $serviceCenter = 'ILORA';
        }

         // Normalize business hub value
        if (strcasecmp($serviceCenter, 'IJEBU IGBO') === 0) {
            $serviceCenter = 'IJEBU-IGBO';
        }

          // Normalize business hub value
        if (strcasecmp($serviceCenter, 'BODE-OLUDE') === 0) {
            $serviceCenter = 'BODE OLUDE';
        }


           // Normalize business hub value
        if (strcasecmp($serviceCenter, 'IDIROKO ROAD') === 0) {
            $serviceCenter = 'IDIROKO';
        }

         // Normalize business hub value
        if (strcasecmp($serviceCenter, 'OKE AGBO') === 0) {
            $serviceCenter = 'OKE-AGBO';
        }

         // Normalize business hub value
        if (strcasecmp($serviceCenter, 'OKE OWA') === 0) {
            $serviceCenter = 'OKE-OWA';
        }

         // Normalize business hub value
        if (strcasecmp($serviceCenter, 'ADO-ODO') === 0) {
            $serviceCenter = 'ADO ODO';
        }

         // Normalize business hub value
        if (strcasecmp($serviceCenter, 'Oke-soda') === 0) {
            $serviceCenter = 'oke-soda';
        }

        return ServiceAreaCode::whereRaw('LOWER(TRIM(Service_Centre)) = ?', [strtolower($serviceCenter)])
            ->whereRaw('LOWER(TRIM(BHUB)) = ?', [strtolower($businessHub)])
            ->where('number_of_customers', '<=', 1000)
            ->first();

    }


     private function getUnusedAccount($servicecode, $udertaking)
    {
       // $url = "http://192.168.15.17:8080/AccountGenerator/webresources/account/unused/114/FX321G9D";  // test api
        //$url = "http://192.168.15.157:9494/AccountGenerator/webresources/account/unused/114/FX321G9D";  // live api
        $url = "http://192.168.15.157:9549/AccountGenerator/webresources/account/unused/114/FX321G9D";  // live api
        $payload = [
            'utid' => $servicecode->AREA_CODE ?? ltrim($udertaking->UTID, '/'),   // "35/52", //  
            'buid' => $servicecode->BUID   // "35A", // 
        ];

          // Use Http::send to make a GET request with a JSON body
        return Http::send('GET', $url, [
            'json' => $payload,
        ])->json();

        //return Http::post($url, $payload)->json();
    }


     private function createNewAccount($servicecode, $dss, $feeder)
    { 

      //  dd($servicecode, $dss, $feeder);
       // $url = "http://emsecmitest:8080/AccountGenerator/webresources/account/generate/114/FX321G9D";  // test api
        $url = "http://192.168.15.157:9549/AccountGenerator/webresources/account/generate/114/FX321G9D";  // live api
        $payload = [
            'utid' => $servicecode->AREA_CODE,
            'buid' => $servicecode->BUID,
            'dssid' => $dss,
            'assetId' => $feeder->Feeder_ID ?? $feeder->FeederID
        ];

       // dd($payload);

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
        
         $url = "http://192.168.15.157:9549/AccountGenerator/webresources/account/save/customer/114/FX321G9D";  // live api

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
        $url = "http://192.168.15.157:9549/AccountGenerator/webresources/account/save/customer/114/FX321G9D";  // live api

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

     // dd($data);
     

        $response = Http::post($url, $data);

        // dd($response->json());   //$response['message']

        if($response['code'] == '400') {
           return false;
        }

        if($response['code'] == '409') {
           return false;
        }

        return $response->successful();

        
        
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


        $checker = Auth::user()->email;

        $checker = $checker == 'victor.ogiogio@ibedc.com'
            ? 'System Generated Account'
            : $checker;

        IbedcPayLogService::create([
                    'module'     => 'Billing',
                    'comment'    => 'Account Successfully Created '. $newAccountNo,
                    'type'       => 'Approved',
                    'module_id'  => $uploadHouses->id,
                    'status'     => 'Completed',
                    'user_email' => $checker, //Auth::user()->email,
         ]);

         
        $user = Auth::user()->email;

      //  $this->programMeter($newAccountNo, $uploadHouses, $account);

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
                'module'     => 'Compliance',
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
                'evaluated' => 'no',
                'billing_comment' => $comment ?? 'Application Request Rejected',
                'lecan_link' => null
            ]);

            $email = $uploadHouses->validated_by;
            $name = Auth::user()->name;

            IbedcPayLogService::create([
                'module' => 'Billing',
                'comment' => $comment .  " - Request Rejected By ({$name})",
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


    public function GenerateAll($trackingId)
    {

    
        $uploadHouses = UploadHouses::where([
            'tracking_id' => $trackingId,
            'evaluated'   => 'yes',
            'status'      => 2,
        ])->whereIn('paid_for_meter', ['Old', 'Yes'])->get();

       

        if ($uploadHouses->isEmpty()) {
            session()->flash('error', 'No data to process.');
            return;
        }

        $hubMap = [
            'ijebu-ode' => 'Ijebu',
            'ilesa'     => 'ILESHA',
            'saapade'   => 'Sapade',
        ];

        $centerMap = [
            'ijebu-ode'    => 'Ijebu',
            'ijebu jesa'   => 'IJEBU JESA',
            'ero omo'      => 'Ero-omo',
            'ijebu igbo'   => 'IJEBU-IGBO',
            'bode-olude'   => 'BODE OLUDE',
            'idiroko road' => 'IDIROKO',
        ];

        $successCount = 0;
        $skippedErrors = [];

       

        foreach ($uploadHouses as $house) {
            if (!empty($house->account_no)) {
                $skippedErrors[] = "Skipped House ID {$house->id}: already has an account number.";
                continue;
            }

            $account = AccoutCreaction::find($house->customer_id);
            if (!$account) {
                $skippedErrors[] = "Skipped House ID {$house->id}: related customer record not found.";
                continue;
            }

            $hubKey = strtolower(trim($house->business_hub));
            $centerKey = strtolower(trim($house->service_center));
            $businessHub = $hubMap[$hubKey] ?? $house->business_hub;
            $serviceCenter = $centerMap[$centerKey] ?? $house->service_center;

            $serviceCode = $this->getAvailableServiceCode($house);

           

            if (!$serviceCode) {
                $skippedErrors[] = "Skipped House ID {$house->id}: exhausted book numbers for SC {$house->service_center}.";
                continue;
            }

           // $buid = BusinessUnit::where('Name', strtoupper($businessHub))->first();

            
            // if (!$buid) {
            //     $skippedErrors[] = "Skipped House ID {$house->id}: business unit not found for hub {$businessHub}.";
            //     continue;
            // }

           

            $dssInfo = DistributionSubStation::where('DistributionID', $house->dss)->first();

        
            if (!$dssInfo) {
                $skippedErrors[] = "Skipped House ID {$house->id}: DSS not found for ID {$house->dss}.";
                continue;
            }

            $feederInfo = Feeder::where('FeederID', $dssInfo->FeederID)->first();
              

            if (!$feederInfo) {
                $skippedErrors[] = "Skipped House ID {$house->id}: feeder not found for FeederID {$dssInfo->FeederID}.";
                continue;
            }

            $tarrif = NewTarrif::where('TariffID', $house->tarrif)->first();

             

            if (!$tarrif) {
                $skippedErrors[] = "Skipped House ID {$house->id}: tariff not found for TariffID {$house->tarrif}.";
                continue;
            }

            $landlordInfo = ContinueAccountCreation::where('tracking_id', $house->tracking_id)->first();
            if (!$landlordInfo) {
                $skippedErrors[] = "Skipped House ID {$house->id}: landlord information missing for tracking ID {$house->tracking_id}.";
                continue;
            }

          

            $fname = !empty($landlordInfo->organisational_name)
                ? $landlordInfo->organisational_name
                : ($landlordInfo->landlord_othernames ?? $account->firstname ?? '');

            $sname = !empty($landlordInfo->organisational_name)
                ? $landlordInfo->landlord_othernames
                : ($landlordInfo->landlord_surname ?? $account->surname ?? '');

            $data = [
                'first_name' => $fname,
                'middle_name' => '',
                'last_name' => $sname,
                'phone' => $landlordInfo->landlord_telephone ?? $account->phone ?? '',
                'email' => $account->email ?? '',
                'nin' => $landlordInfo->nin_number ?? '',
                'gender' => '',
                'dwelling_type' => $house->use_of_premise ?? '',
                'address' => ($house->house_no ?? '') . ' ' . ($house->full_address ?? ''),
                'lga' => $house->lga ?? '',
                'dss_code' => $house->dss ?? '',
                'feeder_code' => $feederInfo->FeederID,
                'tariff' => $tarrif->TariffCode,
                'region' => $house->region ?? '',
                'service_centre' => $serviceCenter,
                'state' => $house->state ?? '',
                'account_type' => 'postpaid',
                'is_md' => 'n',
                'billing_style' => 'fixed',
                'business_hub' => $businessHub,
                'longitude' => substr($house->longitude ?? '', 0, 10),
                'latitude' => substr($house->latitude ?? '', 0, 10),
            ];

           // dd($data);

            $url = 'https://ubvs.ibedc.com/api/integration/generate_account';

            try {
                $createResponse = Http::withToken('muK2zwbzuZtzwKnCQBvSBHVfu7sDOWf3x0ci4Ekbd4767537')
                    ->acceptJson()
                    ->timeout(30)
                    ->connectTimeout(10)
                    ->retry(3, 2000)
                    ->post($url, $data);

                if (!$createResponse->successful()) {
                    $skippedErrors[] = "Skipped House ID {$house->id}: UBVS API error ({$createResponse->status()}): " .
                        ($createResponse->json()['message'] ?? $createResponse->body());
                    continue;
                }

                $responseData = $createResponse->json();

                if (!empty($responseData['payload']['account_number'])) {
                    $newAccountNo = $responseData['payload']['account_number'];
                    $this->finalizeAccount($account, $house, $newAccountNo, $serviceCode);
                    $successCount++;
                    continue;
                }

                if (isset($responseData['err_message'])) {
                    $skippedErrors[] = "Skipped House ID {$house->id}: UBVS error: {$responseData['err_message']}";
                    continue;
                }

                $skippedErrors[] = "Skipped House ID {$house->id}: UBVS responded but no account number was returned.";
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                $msg = $e->getMessage();

                if (str_contains($msg, 'timed out')) {
                    $skippedErrors[] = "Skipped House ID {$house->id}: UBVS request timed out.";
                } elseif (str_contains($msg, 'Connection refused')) {
                    $skippedErrors[] = "Skipped House ID {$house->id}: UBVS server is unreachable.";
                } elseif (str_contains($msg, 'SSL')) {
                    $skippedErrors[] = "Skipped House ID {$house->id}: SSL certificate issue with UBVS.";
                } else {
                    $skippedErrors[] = "Skipped House ID {$house->id}: network error connecting to UBVS.";
                }
            } catch (\Exception $e) {
                $skippedErrors[] = "Skipped House ID {$house->id}: unexpected error: {$e->getMessage()}";
            }
        }

        if ($successCount > 0) {
            session()->flash('success', "Successfully generated {$successCount} customer(s).");
        }

        if (!empty($skippedErrors)) {
            session()->flash('warning', implode(' | ', $skippedErrors));
        }

        if ($successCount === 0 && empty($skippedErrors)) {
            session()->flash('error', 'No valid records were processed.');
        }
    }

  
    public function stageAll($trackingId) {
        // 1. Fetch data
        $uploadHouses = UploadHouses::where([
            'tracking_id' => $trackingId, 
            'evaluated'   => 'yes', 
            'status'      => 2
        ])->whereIn('paid_for_meter', ['Old', 'Yes'])->get();

        if ($uploadHouses->isEmpty()) {
            session()->flash('error', 'No Data to stage.');
            return;
        }

        $hubMap = [
            'ijebu-ode' => 'Ijebu',
            'ilesa'     => 'ILESHA',
            'saapade'   => 'Sapade',
        ];

        $centerMap = [
            'ijebu-ode'    => 'Ijebu',
            'ijebu jesa'   => 'IJEBU JESA',
            'ero omo'      => 'Ero-omo',
            'ijebu igbo'   => 'IJEBU-IGBO',
            'bode-olude'   => 'BODE OLUDE',
            'idiroko road' => 'IDIROKO',
        ];

        $processedCount = 0;
        $skippedErrors = [];

        foreach ($uploadHouses as $house) {
            // Skip already processed
            if ($house->status == 4 || !empty($house->account_no)) {
                continue;
            }

            // 2. Normalize Hub and Center
            $hubKey      = strtolower(trim($house->business_hub));
            $centerKey   = strtolower(trim($house->service_center));
            $businessHub = $hubMap[$hubKey] ?? $house->business_hub;

            // 3. Fetch dependencies
            $serviceCode = $this->getAvailableServiceCode($house);
            if (!$serviceCode) {
                $skippedErrors[] = "Exhausted book numbers for SC: {$house->service_center}";
                continue; 
            }

            $undertaking = UndertakingNumber::whereRaw("TRIM(UTID) = ?", [trim($serviceCode->AREA_CODE)])->first();
            $feeder = DistributionSubStation::where("DistributionID", $house->dss)->first();

            if (!$feeder || !$undertaking) {
                $skippedErrors[] = "Missing DSS or Undertaking info for House ID: {$house->id}";
                continue;
            }

            // 4. Generate Account
            $generateAccount = $this->createNewAccount($serviceCode, $house->dss, $feeder);

            if (isset($generateAccount['error']) && $generateAccount['error'] == true) {
                $skippedErrors[] = "Account Generation Error: " . $generateAccount['message'];
                continue;
            }

            $newAccountNo = $generateAccount['accountNumber'];

            // 5. Final check and Staging
            $exists = UploadHouses::where("account_no", $newAccountNo)->exists();
            if ($exists) {
                $skippedErrors[] = "Account $newAccountNo already exists in system.";
                continue;
            }

            // Assuming $account comes from a relationship or previous lookup
            $account = AccoutCreaction::find($house->customer_id); 
            
            if ($this->creatingStaging($newAccountNo, $account, $house, $serviceCode, $house->dss)) {
                $processedCount++;
            }
        }

        // 6. Final Feedback
        if (count($skippedErrors) > 0) {
            session()->flash('warning', "Processed $processedCount records. Errors: " . implode(', ', $skippedErrors));
        } else {
            session()->flash('success', "Successfully staged $processedCount customers.");
        }
    }


    private function processSingleStaging($account, $uploadHouses) {

        dd($account);
        if ($uploadHouses->status == '4' || $uploadHouses->account_no) {
        return $this->flashError('The request has already been processed: ' . $account->account_no);
        }

        if (!$uploadHouses) {
            return $this->flashError('No Account Result for this customer.');
        }

        dd($account, $uploadHouses);

    }









    public function stageUBVSAccount($id, $aid)
        {

      
            $account = AccoutCreaction::find($id);
            $uploadHouses = UploadHouses::find($aid);

            // ✅ Validate existence FIRST
            if (!$uploadHouses) {
                return $this->flashError('No Account Result found for this customer.');
            }

            if (!$account) {
                return $this->flashError('Account record not found.');
            }

            // ✅ Already processed check
            if ($uploadHouses->status == '4' || $uploadHouses->account_no) {
                return $this->flashError(
                    'The request has already been processed: ' . ($uploadHouses->account_no ?? 'N/A')
                );
            }

            if (!$uploadHouses->tarrif) {
                return $this->flashError(
                    'No Tariff information uploaded by DTM.'
                );
            }

            // Normalize values
            $businessHub = trim($uploadHouses->business_hub);
            $serviceCenter = trim($uploadHouses->service_center);

            if (strcasecmp($serviceCenter, 'Ijebu-Ode') === 0) $serviceCenter = 'Ijebu';
           // if (strcasecmp($businessHub, 'Ijebu-Ode') === 0) $businessHub = 'Ijebu-Ode';
            if (strcasecmp($businessHub, 'Ilesha') === 0) $businessHub = 'Ilesa';

            $buid = BusinessUnit::where("Name", strtoupper($businessHub))->first();

            $servicecode = $this->getAvailableServiceCode($uploadHouses);
            if (!$servicecode) {
                return $this->flashError(
                    'No Service Code available for service center: ' . $uploadHouses->service_center
                );
            }

            $dssInfo = DistributionSubStation::where("DistributionID", $uploadHouses->dss)->first();
            if (!$dssInfo) {
                return $this->flashError(
                    'DSS not found for ID: ' . $uploadHouses->dss
                );
            }

            $feederInfo = Feeder::where("FeederID", $dssInfo->FeederID)->first();
            if (!$feederInfo) {
                return $this->flashError(
                    'Feeder not found for FeederID: ' . $dssInfo->FeederID
                );
            }

            $tarrif = NewTarrif::where("TariffID", $uploadHouses->tarrif)->first();
            if (!$tarrif) {
                return $this->flashError('Invalid tariff configuration on EMS.');
            }

          
            $landlordInfo = ContinueAccountCreation::where("tracking_id", $uploadHouses->tracking_id)->first();

            $fname = !empty($landlordInfo->organisational_name)
                ? $landlordInfo->organisational_name
                : ($landlordInfo->landlord_othernames ?? $account->firstname ?? '');

            $sname = !empty($landlordInfo->organisational_name)
                ? $landlordInfo->landlord_othernames
                : ($landlordInfo->landlord_surname ?? $account->surname ?? '');

            $data = [
                "first_name" => $fname,
                "middle_name" => "",
                "last_name" => $sname,
                "phone" => $landlordInfo->landlord_telephone ?? $account->phone ?? '',
                "email" => $account->email ?? '',
                "nin" => $landlordInfo->nin_number ?? '',
                "gender" => "",
                "dwelling_type" => $uploadHouses->use_of_premise ?? '',
                "address" => ($uploadHouses->house_no ?? '') . ' ' . ($uploadHouses->full_address ?? ''),
                "lga" => $uploadHouses->lga ?? '',
                "dss_code" => $uploadHouses->dss ?? '',
                "feeder_code" => $feederInfo->FeederID,
                "tariff" => $tarrif->TariffCode,
                "region" => $uploadHouses->region ?? '',
                "service_centre" => $serviceCenter,
                "state" => $uploadHouses->state ?? '',
                "account_type" => "postpaid",
                "is_md" => "n",
                "billing_style" => "fixed",
                "business_hub" => $businessHub,
                "longitude" => substr($uploadHouses->longitude ?? '', 0, 10),
                "latitude" => substr($uploadHouses->latitude ?? '', 0, 10),
            ];

         //   dd($data);

            $url = "https://ubvs.ibedc.com/api/integration/generate_account";

            try {

                $createResponse = Http::withToken('muK2zwbzuZtzwKnCQBvSBHVfu7sDOWf3x0ci4Ekbd4767537')
                    ->acceptJson()
                    ->timeout(30)
                    ->connectTimeout(10)
                    ->retry(3, 2000) // 🔥 retry added
                    ->post($url, $data);

                if ($createResponse->successful()) {

                    $responseData = $createResponse->json();

                   // dd($responseData);

                    if (!empty($responseData['payload']['account_number'])) {

                        $newAccountNo = $responseData['payload']['account_number'];

                        $this->finalizeAccount($account, $uploadHouses, $newAccountNo, $servicecode);

                        //Process Programming of Meter
                        //$this->programMeter($newAccountNo, $uploadHouses, $account);

                        Session::flash('success', 'Customer Successfully Generated via UBVS.');

                    } if(isset($responseData['err_message'])) {
                        return $this->flashError(
                            'UBVS Error: ' . $responseData['err_message']
                        );

                    }else {
                        return $this->flashError(
                            'UBVS responded but account number missing.'
                        );
                    }

                } else {
                    return $this->flashError(
                        'UBVS API Error (' . $createResponse->status() . '): ' .
                        ($createResponse->json()['message'] ?? $createResponse->body())
                    );
                }

            } catch (ConnectionException $e) {

                $msg = $e->getMessage();

                if (str_contains($msg, 'timed out')) {
                    return $this->flashError('UBVS request timed out.');
                }

                if (str_contains($msg, 'Connection refused')) {
                    return $this->flashError('UBVS server is unreachable.');
                }

                if (str_contains($msg, 'SSL')) {
                    return $this->flashError('SSL certificate issue with UBVS.');
                }

                return $this->flashError('Network error connecting to UBVS.');

            } catch (\Exception $e) {
                return $this->flashError('Unexpected error: ' . $e->getMessage());
            }
        }


    // public function stageUBVSAccount($id, $aid){

    //     $account = AccoutCreaction::find($id);
    //     $uploadHouses = UploadHouses::find($aid);

        


    //     if ($uploadHouses->status == '4' || $uploadHouses->account_no) {
    //         return $this->flashError('The request has already been processed: ' . $account->account_no);
    //     }

    //     if(!$uploadHouses) {
    //          return $this->flashError('No Account Result for this customer: ' . $uploadHouses->dss);
    //     }


    //     if(!$uploadHouses->tarrif) {
    //          return $this->flashError('No Tariff information for this customer was uploaded by the DTM: ' . $uploadHouses->tariff);
    //     }


    //     $businessHub = trim($uploadHouses->business_hub);  //strtoupper($uploadHouses->business_hub)
    //     $serviceCenter = trim($uploadHouses->service_center);

    //     // Normalize business hub value
    //     if (strcasecmp($serviceCenter, 'Ijebu-Ode') === 0) {
    //         $serviceCenter = 'Ijebu';
    //     }


    //      // Normalize business hub value
    //     if (strcasecmp($businessHub, 'Ijebu-Ode') === 0) {
    //         $businessHub = 'Ijebu';
    //     }

    //     if (strcasecmp($businessHub, 'Ilesa') === 0) {
    //         $businessHub = 'ILESHA';
    //     }

    //      if (strcasecmp($serviceCenter, 'Ijebu jesa') === 0) {
    //         $serviceCenter = 'IJEBU JESA';
    //     }

    //      if (strcasecmp($serviceCenter, 'Ero omo') === 0) {
    //         $serviceCenter = 'Ero-omo';
    //     }


    //      if (strcasecmp($businessHub, 'Saapade') === 0) {
    //         $businessHub = 'Sapade';
    //     }

    //     // if (strcasecmp($businessHub, 'Ile-ife') === 0) {
    //     //     $businessHub = 'ILEIFE';
    //     // }

    //    // ILEIFE //Ile-ife"

    //      // Normalize business hub value
    //     if (strcasecmp($serviceCenter, 'IJEBU IGBO') === 0) {
    //         $serviceCenter = 'IJEBU-IGBO';
    //     }

    //       // Normalize business hub value
    //     if (strcasecmp($serviceCenter, 'BODE-OLUDE') === 0) {
    //         $serviceCenter = 'BODE OLUDE';
    //     }


    //        // Normalize business hub value
    //     if (strcasecmp($serviceCenter, 'IDIROKO ROAD') === 0) {
    //         $serviceCenter = 'IDIROKO';
    //     }

    //     if (strcasecmp($serviceCenter, 'Oke-soda') === 0) {
    //         $serviceCenter = 'oke-soda';
    //     }

    //     $buid = BusinessUnit::where("Name", strtoupper($businessHub))->first();

    //     $servicecode = $this->getAvailableServiceCode($uploadHouses);
       
    //     if (!$servicecode) {
    //         return $this->flashError('All book numbers in the service center are exhausted or No Service Center With That Name. SERVICE CENTER: ' . $uploadHouses->service_center);
    //     }

    //     //Deckstream to provide API to get all Distribution Sub Stations
    //     $dssInfo = DistributionSubStation::where("DistributionID", $uploadHouses->dss)->first();

    //   // dd($dssInfo);

    //     if (!$dssInfo ) {
    //        return $this->flashError(
    //             'DSS information not found in DistributionSubStation in Zone for DSSID: ' . $uploadHouses->dss . '. Please contact Billing.'
    //         );
    //     }

    //     $feederInfo = Feeder::where("FeederID", $dssInfo->FeederID)->first();


    //     //dd($feederInfo);

    //      if (!$feederInfo ) {
    //        return $this->flashError(
    //             'Feeder information not found in DistributionSubStation in Zone for FeederID: ' . $feederInfo->FeederID . '. Please contact Billing.'
    //         );
    //     }


    //     ###################### POSTING TRANSACTIONS TO UBVS FOR ACCOUNT GENERATION ##########################
    //     //$url = "https://ubvstest.ibedc.com/api/integration/generate_account";  // test api 
    //     $url = "https://ubvs.ibedc.com/api/integration/generate_account";  // live api when going live

    //     $landlordInfo = ContinueAccountCreation::where("tracking_id", $uploadHouses->tracking_id)->first();

    //     //dd($account, $uploadHouses, $landlordInfo);
       
    //      // ✅ Use organisation_name if it's not null or empty, else use landlord_othernames
    //     if (!empty($landlordInfo->organisational_name)) {
    //         $fname = $landlordInfo->organisational_name;
    //         $sname = ""; // No surname for organisations
        
    //     } else {
    //         $fname = $landlordInfo->landlord_othernames ?? ($account->firstname ?? '');
    //         $sname = $landlordInfo->landlord_surname ?? ($account->surname ?? '');
    //     }


    //     $lat = substr($uploadHouses->latitude ?? '', 0, 10);
    //     $lng = substr($uploadHouses->longitude ?? '', 0, 10);

    //     $Band = !empty($feederInfo->ServiceID) ? strtoupper(substr($feederInfo->ServiceID, 0, 1))  : '';
    //     $tarrif = NewTarrif::where("TariffID", $uploadHouses->tarrif)->first();

        

        
    //     $data = [

    //         "first_name" => $fname,
    //         "middle_name" => "",
    //         "last_name" => $sname,
    //         "phone" => $landlordInfo->landlord_telephone ?? ($account->phone ?? ''),
    //         "email"=> $account->email ?? '', 
    //         "nin"  => $landlordInfo->nin_number ?? '',
    //         "gender" => "",
    //         "dwelling_type" => $uploadHouses->use_of_premise ?? '',
    //         "address" =>  ($uploadHouses->house_no ?? '') . ' ' . ($uploadHouses->full_address ?? ''),
    //         "lga" => $uploadHouses->lga ?? '',
    //         "dss_code" => $uploadHouses->dss ?? '',
    //         "feeder_code" => $feederInfo->FeederID,
    //         "tariff" =>  $tarrif->TariffCode,
    //         "region" => $uploadHouses->region ?? '',
    //         "service_centre" => $uploadHouses->service_center ?? '',
    //         "state" => $uploadHouses->state ?? '',
    //         "account_type" => "postpaid",
    //         "is_md" => "n",
    //         "billing_style" => "fixed",
    //         "business_hub" => $uploadHouses->business_hub ?? '',
    //         "longitude" => $lat ?? '',
    //         "latitude" => $lng ?? '',

    //     ];

    //     //dd($data);

    //     // Send request
    //     $createResponse = Http::withToken('fKtgjo0gB1Hxx0x5KTRZrT8p2rczI9WBszo6NlRdd90ac4a0')
    //         ->acceptJson()
    //         ->timeout(60)
    //         ->post($url, $data);

    //     // Handle response
    //     if ($createResponse->successful()) {
    //         $responseData = $createResponse->json();

    //         //dd($responseData);
    //         if($responseData['payload']['account_number']) {
    //             $newAccountNo = $responseData['payload']['account_number'];

    //          //   dd($newAccountNo);
    //             $this->finalizeAccount($account, $uploadHouses, $newAccountNo, $servicecode);
    //             Session::flash('success', 'Customer Successfully Generated via UBVS.');
    //         } else {
    //             Session::flash('error', 'Error Generating Account via UBVS. Please contact UBVS.');
    //             return $this->flashError('UBVS API Error: ' . ($createResponse->body() ?? 'Unknown error'));
    //         }

    //     } else {
    //         Session::flash('error', 'Error Generating Account via UBVS. Please try again or contact support if the issue persists.');
    //         return $this->flashError('UBVS API Error: ' . $createResponse->body());
    //     }


    //        // Session::flash('error', 'Error Generating Account via UBVS. Please try again or contact support if the issue persists.');

    // }



    public function stageRequest($id, $aid) {

        $account = AccoutCreaction::find($id);
        $uploadHouses = UploadHouses::find($aid);


         if ($uploadHouses->status == '4' || $uploadHouses->account_no) {
            return $this->flashError('The request has already been processed: ' . $account->account_no);
        }

        if(!$uploadHouses) {
             return $this->flashError('No Account Result for this customer: ' . $uploadHouses->dss);
        }


        $businessHub = trim($uploadHouses->business_hub);  //strtoupper($uploadHouses->business_hub)
        $serviceCenter = trim($uploadHouses->service_center);

        // Normalize business hub value
        if (strcasecmp($serviceCenter, 'Ijebu-Ode') === 0) {
            $serviceCenter = 'Ijebu';
        }


         // Normalize business hub value
        if (strcasecmp($businessHub, 'Ijebu-Ode') === 0) {
            $businessHub = 'Ijebu';
        }

        if (strcasecmp($businessHub, 'Ilesa') === 0) {
            $businessHub = 'ILESHA';
        }

         if (strcasecmp($serviceCenter, 'Ijebu jesa') === 0) {
            $serviceCenter = 'IJEBU JESA';
        }

         if (strcasecmp($serviceCenter, 'Ero omo') === 0) {
            $serviceCenter = 'Ero-omo';
        }


         if (strcasecmp($businessHub, 'Saapade') === 0) {
            $businessHub = 'Sapade';
        }

        // if (strcasecmp($businessHub, 'Ile-ife') === 0) {
        //     $businessHub = 'ILEIFE';
        // }

       // ILEIFE //Ile-ife"

         // Normalize business hub value
        if (strcasecmp($serviceCenter, 'IJEBU IGBO') === 0) {
            $serviceCenter = 'IJEBU-IGBO';
        }

          // Normalize business hub value
        if (strcasecmp($serviceCenter, 'BODE-OLUDE') === 0) {
            $serviceCenter = 'BODE OLUDE';
        }


           // Normalize business hub value
        if (strcasecmp($serviceCenter, 'IDIROKO ROAD') === 0) {
            $serviceCenter = 'IDIROKO';
        }

        if (strcasecmp($serviceCenter, 'Oke-soda') === 0) {
            $serviceCenter = 'oke-soda';
        }

        $buid = BusinessUnit::where("Name", strtoupper($businessHub))->first();

       // dd($buid);
        $servicecode = $this->getAvailableServiceCode($uploadHouses);

      // dd($servicecode->AREA_CODE);

       
        if (!$servicecode) {
            return $this->flashError('All book numbers in the service center are exhausted or No Service Center With That Name. SERVICE CENTER: ' . $uploadHouses->service_center);
        }

        //$udertaking = Undertaking::where("buid", $buid->BUID)->first();
        //$udertaking = Undertaking::where("UTID", trim($servicecode->AREA_CODE))->first();
        $udertaking = UndertakingNumber::whereRaw("TRIM(UTID) = ?", [trim($servicecode->AREA_CODE)])->first();

        //$feeder = DistributionSubStation::where("Assetid", $uploadHouses->dss)->first();
        $feeder = DistributionSubStation::where("DistributionID", $uploadHouses->dss)->first();

        //dd($servicecode->AREA_CODE);
        //dd($buid->BUID);

       // dd($feeder);

        if (!$feeder ) {
           return $this->flashError(
                'DSS information not found in DistributionSubStation in Zone for DSSID: ' . $uploadHouses->dss . '. Please contact Billing.'
            );
        }

        if (!$udertaking) {
            return $this->flashError('Undertaken Not Found in Undertaken Table in Zone.'. $servicecode->AREA_CODE);
        }

        

       // dd($buid, $servicecode, $uploadHouses->dss, $feeder);

      //  dd($uploadHouses->dss);

         $generateAccount = $this->createNewAccount($servicecode, $uploadHouses->dss, $feeder);

        //dd($generateAccount);
          if (isset($generateAccount['error']) && $generateAccount['error'] == true) {

                return $this->flashError($generateAccount['message']);
        }

       
          $checkIfExist = UploadHouses::where("account_no", $generateAccount['accountNumber'])->first();

         if ($checkIfExist) {
            return $this->flashError('Account Exist, please retry ' . $account->account_no);
        }

          //dd($generateAccount);
 
         if (!isset($generateAccount['error']) && isset($generateAccount['accountNumber'])) {
             
              $newAccountNo = $generateAccount['accountNumber'];

              $this->creatingStaging($newAccountNo, $account, $uploadHouses, $servicecode, $uploadHouses->dss);
       
             Session::flash('success', 'Customer Successfully Generated. But Pending On EMS');
         }

          if (isset($generateAccount['error']) && $generateAccount['error'] == true) {

                return $this->flashError($generateAccount['message']);
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


     public function approveaccountbilling($id, $aid){

        $account = AccoutCreaction::find($id);
        $uploadHouses = UploadHouses::find($aid);

        //dd($uploadHouses);

         $user = Auth::user();

         $uploadHouses->update([
                //'evaluated' => "yes",
                 'evaluated' => "approved",
                'evaluated_by' =>  $user->email,
        ]);



        Session::flash('success', 'Customer Successfully Evaluated.');
    }


    public function  move($id) {
       
        $check = UploadHouses::where("id", $id)->first();

        if(!$check) {
             Session::flash('error', 'No Account Result for this customer: ' . $id);
             return;

        }


        $check->update([
                'status' => "1",
        ]);

        IbedcPayLogService::create([
                'module'     => 'Administration',
                'comment'    => "Customer Moved back to DTM By ". Auth::user()->name,
                'type'       => 'Moved',
                'module_id'  => $id,
                'status'     => 'with-dtm',
        ]);

        
        Session::flash('success', 'Customer Successfully Moved to DTM.');
    }



    public function closeAccount($did, $aid){

      //  $account = AccoutCreaction::find($id);
        $uploadHouses = UploadHouses::find($aid);


        if (!$uploadHouses) {
            return redirect()->back()->with('error', 'Account record not found.');
        }

        // Update status before delete
        $uploadHouses->status = 10;
        $uploadHouses->save();
        // OPTIONAL: If you want to also delete the related account
        // $account = AccoutCreaction::find($did);
        // if ($account) {
        //     $account->delete();
        // }

        //dd("The functtion is disabled at the moment");
        // Delete upload house record
        $uploadHouses->delete();
        //$uploadHouse->forceDelete(); // permanent

    return redirect()->back()->with('success', 'Account successfully closed and deleted.');

      

    }



    private function programMeter($newAccountNo, $uploadHouses, $account){

        //check map_id from uploadHouses and get the map details
        if(!$uploadHouses->map_id) {
            Session::flash('error', 'MAP ID not found for this customer. Meter Programming Failed.');
            return ;
        }

         // if exist and account number is allocation and programme is yes return "Meter already programmed"
        if ($uploadHouses->account_no && $uploadHouses->programme == 'Yes') {
             Session::flash('error', 'Meter already programmed.');
            return $this->sendError("Meter already programmed", Response::HTTP_BAD_REQUEST);
        }

           try {
    
                    $response = Http::withHeaders([
                            'Authorization' => 'Bearer LIVEKEY_0XJLDYJZOQWF8UQ9XWVTH',
                            'Accept' => 'application/json',
                        ])->timeout(60)
                        ->acceptJson()
                        ->post('https://msms.ibedc.com/api/v2/setup/fetchdetails', [
                            'map_id' => $uploadHouses->map_id,
                        ]);
    
                    if (!$response->successful()) {
    
                     Session::flash('error', 'Unable to fetch customer details from MSMS');
                        return response()->json([
                            'status'  => false,
                            'message' => 'Unable to fetch customer details from MSMS',
                            'error'   => $response->body(),
                        ], $response->status());
                    }
    
                    $result = $response->json();
    
                    if (!$result['status']) {

                    Session::flash('error', $result['message'] ?? 'Request failed');
                        return response()->json([
                            'status'  => false,
                            'message' => $result['message'] ?? 'Request failed',
                        ], 400);
                    }
    
                    $data = $result['data'];
    
                    $paymentStatus = $data['PaymentInformation']['PaymentStatus'] ?? null;
                    $allocationStatus = $data['AllocationInformation']['AllocationStatus'] ?? null;
                    $installationStatus = $data['InstallationInformation']['InstallationStatus'] ?? null;
                    $MeterNo = $data['InstallationInformation']['MeterNo'] ?? null;
    
                    if ($paymentStatus === 'Completed' && $allocationStatus === 'Meter Allocated' && $installationStatus === 'Meter Installed') {
                        if (empty($uploadHouses->account_no) || empty($MeterNo)) {
                            Session::flash('error', 'Missing account or meter number required for programmin');
                            return response()->json([
                                'status' => false,
                                'message' => 'Missing account or meter number required for programming.',
                            ], 400);
                        }

                        $programUrl = 'https://ubvstest.ibedc.com/api/integration/preprogram_meter';
                        $programPayload = [
                            'meter_number' => $MeterNo,
                            'account_number' => $uploadHouses->account_no,
                        ];

                        $programResponse = Http::withToken('6SZJDBdeKyzQMm94kEnSPdy50YkFgzKmvsNrOMnQ49b6a9c9')
                            ->acceptJson()
                            ->timeout(30)
                            ->retry(3, 2000)
                            ->post($programUrl, $programPayload);

                    
                      //  dd($programResponse->json());

                        if ($programResponse->successful()) {
                            $uploadHouses->update([
                                'programme' => 'Yes',
                                'meterno' => $MeterNo,
                            ]);

                            // We need to notify MSMS that the meter have been programmed successfully, so that 
                            //  they can update their records and trigger any subsequent processes on their end.

                            /*
                                URL: https://msms.ibedc.com/api/v2/setup/notify

                                Request Type: POST

                                Payload:
                                {
                                    "tracking_id": "string",
                                    "map_id": "string",
                                    “meter_no": "string"
                                }
                            */

                         // we are posting to notify MSMS that the meter have been programmed successfully, so that they can update their records and trigger any subsequent processes on their end.
                           
                         try {

                         $MSMSresponse = Http::withHeaders([
                                    'Authorization' => 'Bearer LIVEKEY_0XJLDYJZOQWF8UQ9XWVTH',
                                    'Accept' => 'application/json',
                                ])->timeout(60)
                                ->acceptJson()
                                ->post('https://msms.ibedc.com/api/v2/setup/notify', [
                                    'map_id' => $uploadHouses->map_id,
                                    'tracking_id' => $uploadHouses->id,
                                    'meter_no' => $MeterNo,
                                ]);

                                if( !$MSMSresponse->successful()) {
                                    Session::flash('error', 'Unable to notify MSMS about meter programming');
                                    return response()->json([
                                        'status'  => false,
                                        'message' => 'Unable to notify MSMS about meter programming',
                                        'error'   => $MSMSresponse->body(),
                                    ], $MSMSresponse->status());
                                }

                              // if it it successful
                                $MSMSresult = $MSMSresponse->json();
                             

                         }catch (\Exception $e) {
                            Session::flash('error', 'An error occurred while notifying MSMS: ' . $e->getMessage());
                            return response()->json([
                                'status'  => false,
                                'message' => 'An error occurred while notifying MSMS',
                                'error'   => $e->getMessage(),
                            ], 500);
                         }
                         


                            $recipientEmail = $uploadHouses->validated_by ?: ($account->email ?? null);
                            if (!empty($recipientEmail)) {
                                Mail::raw(
                                    "Dear Customer,\n\nYour meter has been successfully pre-programmed.\nAccount Number: {$uploadHouses->account_no}\nMeter Number: {$MeterNo}\n\nThank you.",
                                    function ($message) use ($recipientEmail) {
                                        $message->to($recipientEmail)
                                            ->subject('Meter Programming Completed');
                                    }
                                );
                            }

                            Session::flash('success', 'Meter programming initiated successfully');

                            return response()->json([
                                'data' => $programResponse->json(),
                                'status' => true,
                                'message' => 'Meter programming initiated successfully',
                            ]);
                        }

                        Session::flash('error', 'Meter programming failed.');
                        return response()->json([
                            'status' => false,
                            'message' => 'Meter programming failed.',
                            'details' => $programResponse->json() ?: $programResponse->body(),
                        ], $programResponse->status() ?: 500);
                    } else {
                        Session::flash('error', 'Meter cannot be programmed. Ensure payment is made, meter is allocated and installed.');
                        return response()->json([
                            'status'  => false,
                            'message' => 'Meter cannot be programmed. Ensure payment is made, meter is allocated and installed.',
                        ], 400);
                    }
    
                } catch (\Exception $e) {
                     Session::flash('error', 'An error occurred while connecting to MSMS');
                    return response()->json([
                        'status'  => false,
                        'message' => 'An error occurred while connecting to MSMS',
                        'error'   => $e->getMessage(),
                    ], 500);
    
                
        }  // end of program meter function for try catch

    }


        


   
 

    public function render()
    {
        return view('livewire.account-details');
    }
}

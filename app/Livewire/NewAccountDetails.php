<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\NAC\Regions;
use App\Models\NAC\DSS;
use App\Models\NAC\UploadHouses;
use Illuminate\Support\Facades\Session;
use App\Models\ECMI\NewTarrif;
use App\Models\ECMI\ServiceClass;
use App\Services\IbedcPayLogService;
use App\Models\NAC\AccoutCreaction;
use Illuminate\Support\Facades\Mail;
use App\Models\IBEDCPayLogs;

class NewAccountDetails extends Component
{

    public $tracking_id;
    public $id;
    public $details;
   // public $region;
    public $comment;

    
    public $businesshub;
    public $servicecenter;
    public $dss;

    public $selectedBusinesshub = null;
    public $selectedServicecenter = null;
    public $selectedDss = null;
    public $newTarriff; 
    public $oldTarriff;
    public $band;
    public $rcomment;

    

    public function mount($id, $tracking_id, $selectedDss = null)
    {
       $this->details = UploadHouses::where(["id" => $id, "tracking_id" => $tracking_id])->first();

        $this->businesshub = Regions::where('region', 'like', "{$this->details->region}%")
        ->distinct()
        ->pluck('Business_Hub');
        $this->servicecenter = collect();
        $this->dss = collect();
        $this->selectedDss = $selectedDss;

       
       // $this->band = ServiceClass::get();
       
    }


   
    public function updateSelectedBusinesshub($businesshub)
        {
             $this->selectedBusinesshub = $businesshub;

            // Only clear selectedServicecenter if it's not in the new list
            $newServiceCenters = DSS::where('hub_name', $businesshub)
                ->whereNotNull('Assetid')
                ->where('Assetid', '!=', '')
                ->select('DSS_11KV_415V_Owner')
                ->distinct()
                ->pluck('DSS_11KV_415V_Owner');

            $this->servicecenter = $newServiceCenters->map(fn($item) => (object)['DSS_11KV_415V_Owner' => $item]);

            if (!$newServiceCenters->contains($this->selectedServicecenter)) {
                $this->selectedServicecenter = null;
                $this->dss = collect();
                $this->selectedDss = null;
            }

        }

        public function updateSelectedservicecenter($servicecenter)
        {
            $this->selectedDss = null;

       

            $state = ($this->details->region == "IBADAN") ? "OYO" : $this->details->region;

             if ($this->selectedBusinesshub && $servicecenter && $this->details?->region) {
                $this->dss = DSS::where('hub_name', $this->selectedBusinesshub)
                    ->where('DSS_11KV_415V_Owner', $servicecenter)
                    ->where('Dss_State', $state)
                   // ->whereNotNull('Assetid')
                    ->get();
                    
            } else {
                $this->dss = collect();
            }

            // dd($this->selectedBusinesshub, $servicecenter, $this->details?->region, $this->dss);


            // if (!is_null($servicecenter)) {
            //     $this->dss = DSS::where('DSS_11KV_415V_Owner', $servicecenter)->get();
                
            // } else {
            //     $this->dss = collect
            // }
        }


        public function submit() {

         // dd($this->selectedBusinesshub, $this->selectedServicecenter,  $this->selectedDss);
           $cheking = UploadHouses::where("id", $this->id)->first();
           if($cheking->status == 1) {
             Session::flash('error', 'Customer Mapping have already been completed');
           }

           if(!$this->newTarriff  || !$this->oldTarriff || !$this->band){
             Session::flash('error', 'Please select all fields');
           }


           $updated = UploadHouses::where("id", $this->id)->update([
                'business_hub' => $this->selectedBusinesshub,
                'service_center' => $this->selectedServicecenter,
                'dss' => $this->selectedDss,
                'status' => 1,
                'validated_by' => auth()->user()->id, 
                'comment' => $this->comment,
                'service_class' => $this->band,
                'tarrif' => $this->newTarriff,
                'old_tarriff' => NewTarrif::where("TariffID", $this->oldTarriff)->value("OldTariffCode"),
                'tarrif_id' => $this->oldTarriff
            ]);

            Session::flash('success', 'Customer Successfully Mapped.');
            
        }

   


   public function rejectdtmrequest() {

     $uploadHouses = UploadHouses::where("id", $this->id)->first();

    if (!$uploadHouses) {
        Session::flash('error', 'Account not found.');
        return;
    }

    $uploadHouses->update([
        'status' => 5,
        // 'billing_comment' => 'Application Request Rejected By Billing',
        'lecan_link' => NULL
    ]);

        $account = AccoutCreaction::where("tracking_id", $uploadHouses->tracking_id)->first();
        
        if ($account) {
            // Update status of the account
            $account->update([
                'status' => 'started',
                'status_name' => 'rejected',
                'comment' => $this->rcomment
            ]);

            IbedcPayLogService::create([
                'module'     => 'New Account',
                'comment'    => $this->rcomment,
                'type'       => 'Rejected',
                'module_id'  => $this->id,
                'status'     => 'started',
            ]);

            $email = $uploadHouses->validated_by;

            if (!empty($email)) {
                // Send email with token
                Mail::raw(
                    "Your request with tracking ID was rejected. Tracking ID is: {$uploadHouses->tracking_id} Comments: {$this->rcomment}",
                    function ($message) use ($email) {
                        $message->to($email)
                                ->subject('New Account Request Rejected');
                    }
                );
            }

            // // Send email with token
            // Mail::raw("Your request with tracking ID was rejected. Tracking ID is: {$uploadHouses->tracking_id} Comments: { $this->rcomment }", function ($message) use ($email) {
            //     $message->to($email)
            //             ->subject('New Account Request Rejected');
            // });

            Session::flash('success', 'New Account Successfully Rejected By .');
        } 
        
   // dd($this->rcomment);
   }

    public function render()
    {
        $logs = IBEDCPayLogs::where("module_id", $this->id)->get();

        return view('livewire.new-account-details', [
             'tarriff' => NewTarrif::get(),
             'iband' => ServiceClass::get(),
             'logs' => $logs
        ]);
    }
}

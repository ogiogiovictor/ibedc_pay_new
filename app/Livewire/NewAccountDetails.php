<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\NAC\Regions;
use App\Models\NAC\DSS;
use App\Models\NAC\UploadHouses;
use Illuminate\Support\Facades\Session;


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
    

    public function mount($id, $tracking_id, $selectedDss = null)
    {
       $this->details = UploadHouses::where(["id" => $id, "tracking_id" => $tracking_id])->first();

        $this->businesshub = Regions::where('region', 'like', "{$this->details->region}%")
        ->distinct()
        ->pluck('Business_Hub');
        $this->servicecenter = collect();
        $this->dss = collect();
        $this->selectedDss = $selectedDss;
       
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

           $updated = UploadHouses::where("id", $this->id)->update([
                'business_hub' => $this->selectedBusinesshub,
                'service_center' => $this->selectedServicecenter,
                'dss' => $this->selectedDss,
                'status' => 1,
                'validated_by' => auth()->user()->id, 
                'comment' => $this->comment
            ]);

            Session::flash('success', 'Customer Successfully Mapped.');
            
        }

   


    public function render()
    {
        return view('livewire.new-account-details');
    }
}

<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\NAC\Regions;
use App\Models\NAC\DSS;

class TrackingDetails extends Component
{
    public $tracking;
    public $id;

    public $validationcode;

    public $businesshub;
    public $servicecenter;
    public $dss;

    public $selectedBusinesshub = null;
    public $selectedServicecenter = null;
    public $selectedDss = null;



    public function mount($selectedDss = null)
    {
        $this->tracking = session('tracking_data');

        if (!isset($this->tracking->region)) {
            abort(403, 'No Business hubs found for the tracking ID'); // Redirect back
        }

         $this->businesshub = Regions::where('region', $this->tracking->region)
        ->distinct()
        ->pluck("Business_Hub");

        $this->servicecenter = collect();
        $this->dss = collect();
        $this->selectedDss = $selectedDss;


        // if (!is_null($selectedDss)) {
        //     $city = City::with('state.country')->find($selectedCity);
        //     if ($city) {
        //         $this->cities = City::where('state_id', $city->state_id)->get();
        //         $this->states = State::where('country_id', $city->state->country_id)->get();
        //         $this->selectedCountry = $city->state->country_id;
        //         $this->selectedState = $city->state_id;
        //     }
        // }

        if (!$this->tracking) {
            abort(403, 'No tracking data found.');
        }

        // Optional: Clear it after reading
        session()->forget('tracking_data');
    }


    public function updateSelectedBusinesshub($businesshub) {

        //  $this->servicecenter = DSS::where('hub_name', $businesshub)
        //     ->select('DSS_11KV_415V_Owner')
        //     ->distinct()
        //     ->pluck('DSS_11KV_415V_Owner')
        //     ->filter()
        //     ->values();

        // $this->servicecenter = DSS::where('hub_name', $businesshub)
        // ->select('DSS_11KV_415V_Owner')
        // ->distinct()
        // ->get();
        $this->servicecenter = DSS::where('hub_name', $businesshub)
        ->whereNotNull('Assetid')
        ->where('Assetid', '!=', '')
        ->select('DSS_11KV_415V_Owner')
        ->distinct()
        ->get();

         $this->selectedServicecenter = NULL;

    }

    public function updateSelectedservicecenter($servicecenter) {

        if(!is_null($servicecenter)) {

            $this->dss = DSS::where('AssetId', $servicecenter)->get();
        }
    }



    public function render()
    {
        return view('livewire.tracking-details', [
            'tracking' => $this->tracking]);
    }
}

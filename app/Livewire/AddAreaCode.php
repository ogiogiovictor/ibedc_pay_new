<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\EMS\BusinessUnit;
use App\Models\ServiceCenterArea; 

class AddAreaCode extends Component
{

    public $region;
    public $bhub;
    public $service_center;
    public $area_code;
    public $dtm_email;

    public $buid; // for business hubs list

    public function mount() {
        $this->buid = BusinessUnit::orderby("Name", "asc")->get();
    }

     public function addAreaCode()
    {
        $this->validate([
            'region' => 'required|string',
            'bhub' => 'required|string',
            'service_center' => 'required|string|max:255',
            'area_code' => 'required|string|max:6',
            'dtm_email' => 'email|required'
        // 'area_code' => 'required|string|max:6|unique:area_code_service_center,area_code',
        ]);

        ServiceCenterArea::create([
            'State' => $this->region,
            'BUID' => BusinessUnit::where("Name", $this->bhub)->value("BUID"),
            'BHUB' => $this->bhub,
            'Service_Centre' => $this->service_center,
            'AREA_CODE' => $this->area_code,
            'number_of_customers' => 0,
            'dtm_emails' => $this->dtm_email,
        ]);

        session()->flash('success', 'Area Code added successfully.');

        // clear form
        $this->reset(['region', 'bhub', 'service_center', 'area_code']);
    }

    public function render()
    {
        return view('livewire.add-area-code');
    }
}

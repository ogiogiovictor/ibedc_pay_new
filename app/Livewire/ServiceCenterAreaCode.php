<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ServiceCenterArea;
use Illuminate\Support\Facades\DB;

class ServiceCenterAreaCode extends Component
{
    public $allcenters;
    public $perPage = 30;
    public $clearOption; // dropdown field
    public $clearValue;  // input field

  

    public function mount() {

       $this->allcenters = DB::table('area_code_service_center')->orderBy('created_at', 'desc')->limit($this->perPage)->get();


    }

     public function searchTransactions(){
       
    
        // Validate that if either is filled, both must be filled
    if ((empty($this->clearOption) && !empty($this->clearValue)) || (!empty($this->clearOption) && empty($this->clearValue))) {
        session()->flash('error', 'Both "Select" and "Enter Value" fields are required for search.');
        return;
    }

     // Start query
        $query = DB::table('area_code_service_center');

        // Apply search filter
        if (!empty($this->clearOption) && !empty($this->clearValue)) {
            $query->where($this->clearOption, 'like', '%' . $this->clearValue . '%');
        }

        // Fetch data with limit (no 'id' column ordering)
        $this->allcenters = $query->limit($this->perPage)->get();

    }


  


    public function render()
    {
       
        // return view('livewire.service-center-area-code', [
        //     'allcenters' => $this->allcenters,
        // ]);
       return view('livewire.service-center-area-code');
    }
}

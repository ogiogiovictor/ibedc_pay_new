<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\CommissionSettings as Commission;
use Illuminate\Support\Facades\Session;

class CommissionSettings extends Component
{

    public $commission;


    public function mount() {

        $this->commission = Commission::all();
    }


    public function render()
    {
        return view('livewire.commission-settings');
    }
}

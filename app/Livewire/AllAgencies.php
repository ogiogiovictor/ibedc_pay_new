<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Agency\Agents;

class AllAgencies extends Component
{
    public $agencies;
    public $agencyCount;

    public function mount() {

        $this->agencies = Agents::all();
        $this->agencyCount = $this->agencies->count(); // Corrected line
    }


    public function render()
    {
        return view('livewire.all-agencies');
    }
}

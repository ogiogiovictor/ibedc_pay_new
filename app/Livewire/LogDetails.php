<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Logs\AppLog; 

class LogDetails extends Component
{
   
    public $details;

    public function mount($id)
    {
        $this->details = AppLog::findOrFail($id); // Fetch details based on ID
        
    }


    public function render()
    {
        return view('livewire.log-details');
    }
}

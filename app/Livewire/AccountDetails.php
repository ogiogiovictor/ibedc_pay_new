<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\NAC\AccoutCreaction;
use App\Models\NAC\UploadAccountCreation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Enums\RoleEnum;

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


    public function render()
    {
        return view('livewire.account-details');
    }
}

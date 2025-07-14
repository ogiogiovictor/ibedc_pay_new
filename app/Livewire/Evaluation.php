<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Enums\RoleEnum;
use Illuminate\Support\Facades\Session;
use App\Models\NAC\UploadHouses;
use App\Models\NAC\AccoutCreaction;
use App\Models\NAC\UploadAccountCreation;
use Illuminate\Support\Facades\DB;

class Evaluation extends Component
{

    public $tracking_id;
    public $tracking;

     public function mount($tracking_id)
    {
         $user = Auth::user();

        //  dd($user->authority);

        if( $user->authority != RoleEnum::mso()->value &&  $user->authority != RoleEnum::super_admin()->value ) {
          //redirect to agency dashboard
          abort(403, 'Unathorized action.');
        } 



         $customers = new AccoutCreaction();

         $this->tracking = $customers
            ->with(['continuation', 'uploadinformation', 'caccounts', 'uploadedPictures'])
            ->where('tracking_id', $tracking_id)
            ->first();
    }



    public function render()
    {
        return view('livewire.evaluation');
    }
}

<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Agency\Agents;
use Illuminate\Support\Facades\Auth;
use App\Enums\RoleEnum;


class AllAgencies extends Component
{
    public $agencies;
    public $agencyCount;
    public $user;

    public function mount() {
        $user = Auth::user();

        if ($user->authority == RoleEnum::super_admin()->value || $user->authority == RoleEnum::admin()->value) {
            $this->agencies = Agents::all();
            $this->agencyCount = $this->agencies->count();
        } elseif ($user->authority == RoleEnum::agency_admin()->value) {
            $this->agencies = Agents::where('id', $user->agency)->get();
            $this->agencyCount = $this->agencies->count();
        } else {
            abort(403, "You do not have access to this resource");
        }
    
        
       
    }


    public function render()
    {
        return view('livewire.all-agencies');
    }
}

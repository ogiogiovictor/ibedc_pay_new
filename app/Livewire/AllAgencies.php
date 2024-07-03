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

        if($user->authority == (RoleEnum::super_admin()->value || RoleEnum::super_admin()->value)) {
            $this->agencies = Agents::all();
        } else {
            $this->agencies = Agents::where(["id" => $user->agency, "authority" => RoleEnum::agency_admin()->value])->get();
        }
        
        $this->agencyCount = $this->agencies->count(); // Corrected line
    }


    public function render()
    {
        return view('livewire.all-agencies');
    }
}

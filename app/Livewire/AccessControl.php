<?php

namespace App\Livewire;

use Livewire\Component;
use Spatie\Permission\Models\Role;


class AccessControl extends Component
{
    public $rolesWithUserCount;

    public function mount(){

        $this->rolesWithUserCount = Role::withCount('users')->get();

    }


    public function render()
    {
        return view('livewire.access-control');
    }
}

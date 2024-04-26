<?php

namespace App\Livewire;

use Livewire\Component;
use Spatie\Permission\Models\Role;

class CreateRole extends Component
{

    public $roles;

    public function mount() {

        $this->roles = Role::all();
    }


    public function render()
    {
        return view('livewire.create-role');
    }
}

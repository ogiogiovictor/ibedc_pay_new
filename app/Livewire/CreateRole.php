<?php

namespace App\Livewire;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Session;

class CreateRole extends Component
{

    public $roles;
    public $role_name;

    public function mount() {

        $this->roles = Role::all();
    }

    public function addRole() {

        if(!$this->role_name){

            Session::flash('error', 'Please enter role name');
            return;
        }

        $role = new Role();

        $role->name = $this->role_name;
        $role->guard_name = "web";

        $role->save();

        Session::flash('success', 'Successfully Added.');
    }


    public function render()
    {
        return view('livewire.create-role');
    }
}

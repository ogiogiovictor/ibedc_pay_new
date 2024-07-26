<?php

namespace App\Livewire;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use App\Livewire\AuthorizeTransactions;
use App\Enums\RoleEnum;


class AccessControl extends Component
{
    public $rolesWithUserCount;
    public $user;
    public $access;

    public function mount(){


        $this->user = Auth::user();

        if ($this->user->authority != RoleEnum::super_admin()->value) {
            abort(403, "You do not have access to this resource");
        }

        //$this->rolesWithUserCount = Role::withCount('users')->get();
        $this->rolesWithUserCount = Role::all();

       // dd($this->rolesWithUserCount);

    }


    public function render()
    {
        return view('livewire.access-control');
    }
}

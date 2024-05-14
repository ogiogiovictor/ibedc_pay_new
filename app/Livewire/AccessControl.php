<?php

namespace App\Livewire;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use App\Livewire\AuthorizeTransactions;


class AccessControl extends Component
{
    public $rolesWithUserCount;

    public function mount(){


        $this->user = Auth::user();

        $this->acesss = AuthorizeTransactions::authorizeTransaction($this->user);

        $this->rolesWithUserCount = Role::withCount('users')->get();

        //dd($this->rolesWithUserCount);

    }


    public function render()
    {
        return view('livewire.access-control');
    }
}

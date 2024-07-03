<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;

class Users extends Component
{
    public $users;

    public function mount()
    {
        $this->users = User::orderby("id", "desc")->paginate(15)->toArray();
    }

    
    public function render()
    {
        return view('livewire.users');
    }
}

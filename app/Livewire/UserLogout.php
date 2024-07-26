<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class UserLogout extends Component
{
    public function logout() {

        Auth::logout();
       // auth()->logout();
        return redirect('/login');
    }


    public function render()
    {
        return view('livewire.user-logout');
    }
}

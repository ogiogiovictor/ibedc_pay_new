<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Login extends Component
{
    public $form = [
        'email' => '',
        'password' => ''
    ];

    public function submit() {

        $this->validate([
            'form.email' =>  'required|email',
            'form.password' => 'required'
        ]);

        if (Auth::attempt($this->form)) {
            // Authentication successful
            $user = Auth::user();

            if($user->status != 1) {
                Auth::logout();
                session()->flash('error', 'Your account is not active. Please contact the administrator.');
                return redirect()->back();
            }

            if ($user->isSuperAdmin()) {
                // Redirect admin user to the dashboard
                return redirect()->route('dashboard');
            } else {
                // Logout non-admin users
                Auth::logout();
                session()->flash('error', 'You do not have permission to access this page.');
                return redirect()->back();
            }
        } else {
            // Authentication failed
            session()->flash('error', 'Invalid credentials. Please try again.');
            return redirect()->back();
        }
    }

    public function render()
    {
        return view('livewire.login');
    }
}

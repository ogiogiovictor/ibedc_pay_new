<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\AuditLogService;

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

          

            if ($user->isSuperAdmin() || $user->isAdmin() || $user->isManager() || $user->isSupervisor() 
            || $user->isPaymentChannel() || $user->isManager() ) {
                
               AuditLogService::logAction('User Login', $user->authority, 'User logged in successfully', $user->id, 200);

                // Redirect admin user to the dashboard
                return redirect()->route('dashboard');

            } else if($user->isAgencyAdmin()) {

                session()->flash('success', 'You are successfully loggedIn');

                AuditLogService::logAction('User Login', $user->authority, 'User logged in successfully', $user->id, 200);

                return redirect()->route('agency_dashboard');
                
            } else {
                // Logout non-admin users
                Auth::logout();
                session()->flash('error', 'You do not have permission to access this page.');
                AuditLogService::logAction('User Logged Out', $user->authority, 'You do not have permission to access this page.', $user->id, 200);
                return redirect()->back();
            }
        } else {
            // Authentication failed
            session()->flash('error', 'Invalid credentials. Please try again.');
            AuditLogService::logAction('User Logged Out', null, 'You do not have permission to access this page.', 0, 200);
            return redirect()->back();
        }
    }

    public function render()
    {
        return view('livewire.login');
    }
}

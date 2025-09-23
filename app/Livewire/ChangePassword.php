<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserAccountCreated;


class ChangePassword extends Component
{
    

    public $id;
    public $email;

    public $user_email, $password, $password1;


    public function mount() {

        $this->email = Auth::user()->email;
        $this->user_email = $this->email;

         // Redirect immediately if user already changed password
        $user = Auth::user();
        if ($user->default_password == 0) {
            return $this->redirect(route('customers_pending_accounts'), navigate: true);
        }
    }


     public function changePassword()
    {
        // Validate inputs
        $this->validate([
            'user_email' => 'required|email|exists:users,email',
            'password'   => 'required|string|min:6',
            'password1'  => 'required|string|same:password',
        ]);

        // Find user
        $user = User::where('email', $this->user_email)->first();

        if (!$user) {
            session()->flash('error', 'User not found.');
            return;
        }

        // Update password
        $user->password = Hash::make($this->password);
        $user->default_password = 0;
        $user->save();

        // Send email with account details
         Mail::to($user->email)->send(new UserAccountCreated($user, $this->password));


        // Flash success
        session()->flash('success', 'Password changed successfully.');

         // Use Livewire redirect
        return $this->redirect(route('customers_pending_accounts'), navigate: true);

        // Reset fields
        $this->reset(['user_email', 'password', 'password1']);
    }


    public function render()
    {
        return view('livewire.change-password');
    }
}

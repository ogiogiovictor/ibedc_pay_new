<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\EMS\BusinessUnit;
use App\Models\ServiceCenterArea; 
use App\Models\NAC\DSS;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserAccountCreated;

class AddUser extends Component
{

    public $user_name, $user_email, $user_phone, $region, $account_type, $bhub, $service_center, $authority, $password;
    public $buid, $get_service;


    public function mount() {
        $this->buid = BusinessUnit::orderby("Name", "asc")->get();
        $this->get_service = DSS::select("DSS_11KV_415V_Owner")->orderby("DSS_11KV_415V_Owner", "asc")->distinct()->get();
    }

    public function addUser()
    {

        $this->validate([
            'user_name'      => 'required|string|max:255',
            'user_email'     => 'required|email|unique:users,email',
            'user_phone'     => 'required|string|unique:users,phone|max:20',
            'region'         => 'required|string',
           // 'account_type'   => 'required|string|in:prepaid,postpaid',
            //'bhub'           => 'required|string',
           // 'service_center' => 'required|string',
            'authority' => 'required|string',
            'password'  => 'required|string',
        ]);


        $plainPassword = $this->password;

        $user = User::create([
            'name'      => $this->user_name,
            'email'     => trim($this->user_email),
            'phone'     => $this->user_phone,
            'region'         => $this->region,
            'account_type'   => $this->account_type,
            'business_hub'   => $this->bhub,
            'service_center' => $this->service_center,
            'authority' => $this->authority,
            'account_type' => $this->account_type,
            'agency' => 0,
            'status' => 1,
            'password' => Hash::make($plainPassword),
            'default_password' => 1
        ]);

         // Assign role if authority exists
        if (isset($this->authority)) {
                $user->assignRole(strtolower($this->authority));
        }

         // Send email with account details
         Mail::to($user->email)->send(new UserAccountCreated($user, $plainPassword));


        session()->flash('success', 'User added successfully.');

        $this->reset(['user_name','user_email','user_phone','region','account_type','bhub','service_center']);

    }

    public function render()
    {
        return view('livewire.add-user');
    }
}

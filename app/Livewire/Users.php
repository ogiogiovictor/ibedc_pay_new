<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Enums\RoleEnum;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserAccountCreated;
use Illuminate\Support\Facades\Hash;

class Users extends Component
{
    public $users;
    public $clearOption;
    public $clearValue;

    public function mount()
    {
        $user = Auth::user();

        if($user->authority == (RoleEnum::agency_admin()->value )) {

            $this->users = User::where("agency", $user->agency)->orderby("id", "desc")->paginate(15)->toArray();

        }else if($user->authority == (RoleEnum::super_admin()->value || RoleEnum::admin()->value)) {
            $this->users = User::orderby("id", "desc")->paginate(30)->toArray();
        } else {
            abort('403', "You do not have access to this resource");
        }
       
        
    }


    public function searchUser() {

        if (!$this->clearOption) {
            session()->flash('error', 'Please select an option');
            return;
        }


        if ($this->clearOption && $this->clearValue) {
            $option = $this->clearOption;
            $value = $this->clearValue;
        }

        $this->users = User::query()
            ->when($this->clearOption && $this->clearValue, function ($query) {
                //$query->where($this->clearOption, '=', $this->clearValue);
                $query->where($this->clearOption, 'like', '%' . $this->clearValue . '%');
            })
           ->orderByDesc('created_at') // Assuming 'created_at' is the column you want to order by
            ->paginate(10)->toArray();

           // dd($this->users);

        // if ($this->users->isEmpty()) {
        //     $this->users = collect();
        // }


    }


    public function resetUser($id) {
        // Find user
        $user = User::findOrFail($id);

        // Generate a new random password
        $plainPassword = Str::random(10); // e.g. 10 characters long

        // Update user password (hashed)
        $user->update([
            'password' => Hash::make($plainPassword),
        ]);

        // Resend the mail with updated credentials
        Mail::to($user->email)->send(new UserAccountCreated($user, $plainPassword));

        session()->flash('success', 'User password has been reset and sent to their email.');

    }

    
    public function render()
    {
        return view('livewire.users');
    }
}

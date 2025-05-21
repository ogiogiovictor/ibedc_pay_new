<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Wallet\WalletHistory;
use App\Models\Wallet\WalletUser;
use App\Models\User;


class WalletAccount extends Component
{
    public $users;
    public $clearOption;
    public $clearValue;


    public function mount() {

        $user = new WalletUser();
        $users = $user->orderByRaw("
                CASE 
                WHEN wallet_amount != 0 THEN 1 
                ELSE 0 
                END DESC
            ")
            ->orderBy('created_at', 'desc')
            ->paginate(30);

            // Ensure a consistent structure for $this->users
    $this->users = [
        'data' => $users->items(),
        'links' => $users->toArray()['links'] ?? [],
    ];

    }



    public function searchUserWallet() {

       if (!$this->clearOption) {
        session()->flash('error', 'Please select an option');
        return;
    }

    $useremail = User::where("email", $this->clearValue)->first();
    //dd($this->clearOption);
    if(!$useremail) {
        session()->flash('error', 'Please select an option');
        return;
    }

    if ($this->clearOption && $this->clearValue) {
        $option = $this->clearOption;
        $value = $this->clearValue;
    }

    // $users = WalletUser::query()
    //     ->when($this->clearOption && $this->clearValue, function ($query, $useremail) {
    //         $query->where($this->clearOption, '=', $useremail->id);
    //     })
    //     ->orderByDesc('created_at') // Assuming 'created_at' is the column you want to order by
    //     ->paginate(10);

        $users = WalletUser::query()
            ->where($this->clearOption, '=', $useremail->id) // Ensure $useremail is valid
            ->orderByDesc('created_at')
            ->paginate(10);

    // Ensure consistent structure for $this->users
    $this->users = [
        'data' => $users->items(),
        'links' => $users->toArray()['links'] ?? [],
    ];

    if (empty($this->users['data'])) {
        $this->users['data'] = [];
    }

    }


    public function render()
    {
        return view('livewire.wallet-account');
    }
}

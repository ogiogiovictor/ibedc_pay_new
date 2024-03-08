<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Wallet\WalletUser;
use App\Models\Wallet\WalletHistory;

class Wallets extends Component
{
    public $walet_users;

    public function mount()
    {
        $this->walet_users = WalletUser::with('myhistory')->paginate(25)->toArray();
    }


    public function render()
    {
        return view('livewire.wallets');
    }
}

<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Wallet\WalletHistory;
use App\Models\Wallet\WalletUser;
use App\Models\User;
use App\Models\VirtualAccount;
use App\Models\VirtualAccountTrasactions;
use App\Models\Transactions\PaymentTransactions;

class WalletDetaisls extends Component
{
    public $id;
    public $user_id;
    public $data;


    public function mount() {

        // Fetch wallet transaction
        $walletTransaction = WalletHistory::find($this->id);

        // Fetch user details
        $user = User::find($this->user_id);

        // Initialize data array
        $this->data = [
            'walletTransaction' => $walletTransaction,
            'user' => $user,
            'wallet' => null,
            'virtualAccount' => null,
            'virtualAccountTransactions' => [],
            'userwallethistory' => [],
            'paymenthistory' => []
        ];

        if ($user) {
            // Fetch wallet details
            $this->data['wallet'] = WalletUser::where('user_id', $this->user_id)->first();

            // Fetch virtual account details
            $this->data['virtualAccount'] = VirtualAccount::where('user_id', $this->user_id)->orderby("created_at", "desc")->first();

            // Fetch virtual account transactions
            $this->data['virtualAccountTransactions'] = VirtualAccountTrasactions::where('customer_email', $user->email)->orderby("created_at", "desc")->get();

            $this->data['userwallethistory'] = WalletHistory::where('user_id', $this->user_id)->orderby("created_at", "desc")->get();

            $this->data['paymenthistory'] = PaymentTransactions::whereIn("status", ['processing', 'failed', 'success', 'cancelled'])->where('user_id', $this->user_id)->orderby("created_at", "desc")->get();
        }


     //  dd($this->data);

    }

    public function render()
    {
        return view('livewire.wallet-detaisls');
    }
}

<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Wallet\WalletHistory;
use App\Models\Wallet\WalletUser;
use App\Models\User;
use App\Models\CustomerAccount;
use App\Models\VirtualAccount;
use App\Models\VirtualAccountTrasactions;
use App\Models\Transactions\PaymentTransactions;

class UserVirtualAccountDetails extends Component
{

    public $id;
    public $user_id; 
    public $email;
    
    public $data;
    

    public function mount(){
        
        $virtual_account = VirtualAccount::where(["id" => $this->id, "user_id" => $this->user_id, "customer_email" => $this->email])->first();

       // $user = User::find($this->user_id);
        $user = User::find($this->user_id) ?? CustomerAccount::find($this->user_id);


        // Initialize data array
        $this->data = [
            'virtualAccount' => $virtual_account,
            'user' => $user,
            'walletTransaction' => [],
            'wallet' => null,
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


    }


    public function render()
    {
        return view('livewire.user-virtual-account-details');
    }
}

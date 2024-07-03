<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transactions\PaymentTransactions;

class TransactionDetails extends Component
{
    public $transaction_id;
    public $all_transactions;


    public function mount() {

        $transaction = new PaymentTransactions();
        $this->all_transactions = $transaction->where("transaction_id", $this->transaction_id)->first();

    }


    public function render()
    {
        return view('livewire.transaction-details');
    }
}

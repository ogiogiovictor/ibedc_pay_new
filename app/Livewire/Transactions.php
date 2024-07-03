<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transactions\PaymentTransactions;


class Transactions extends Component
{
    public $all_transactions;

    public function mount()
    {
        $transaction = new PaymentTransactions();
        //All Transactions
        $this->all_transactions = $transaction->orderby("id", "desc")->paginate(50)->toArray();


    }

    public function render()
    {
        return view('livewire.transactions');
    }
}

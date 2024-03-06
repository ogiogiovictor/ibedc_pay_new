<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transactions\PaymentTransactions;

class Dashboard extends Component
{

    public $transactions;
    public $count_transactions;

    public function mount()
    {
        $transaction = new PaymentTransactions();
        $this->transactions = $transaction->sumTodaySales();
        $this->count_transactions = $transaction->countTodaysTransaction();

    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}

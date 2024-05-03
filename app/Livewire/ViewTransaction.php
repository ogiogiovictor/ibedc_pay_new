<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transactions\PayTransactions;

class ViewTransaction extends Component
{
    public $transactions = [];

    public function mount() {

        $this->transactions = PayTransactions::where("transaction_id", $this->transactions)->first();

    }

    public function processTransaction($id){
        dd($id);
    }


    public function render()
    {
        return view('livewire.view-transaction');
    }
}

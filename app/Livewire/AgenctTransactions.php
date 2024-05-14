<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transactions\PaymentTransactions;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;


class AgenctTransactions extends Component
{
    use WithPagination;

    public $id;
    public $transactions = [];
    public $started = "";
    public $processing = "";
    public $success = "";
    public $totalCollection = "";
    public $monthlyCollection = "";
    public $todaysCollection = "";


    public function mount() {

        $user = Auth::user();
        $this->transactions = PaymentTransactions::where(["agency" => $this->id, 'agency' => $user->agency])->orderby("created_at", "desc")->paginate(10)->toArray();
       

    }


    public function render()
    {

        return view('livewire.agenct-transactions');
    }
}

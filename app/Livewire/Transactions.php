<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transactions\PaymentTransactions;
use Illuminate\Support\Facades\Auth;
use App\Enums\RoleEnum;


class Transactions extends Component
{
    public $all_transactions;

    public function mount()
    {
        $transaction = new PaymentTransactions();
        //All Transactions
        $user = Auth::user();

        if($user->authority == (RoleEnum::agency_admin()->value )) {
            $this->all_transactions = $transaction->where("agency", $user->agency)->orderby("id", "desc")->paginate(50)->toArray();
        }else {
            $this->all_transactions = $transaction->orderby("id", "desc")->paginate(50)->toArray();
        }
       


    }

    public function render()
    {
        return view('livewire.transactions');
    }
}

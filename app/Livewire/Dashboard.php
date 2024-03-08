<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transactions\PaymentTransactions;
use App\Models\User;
use App\Models\ContactUs;

class Dashboard extends Component
{

    public $transactions;
    public $count_transactions;
    public $users;
    public $complaints;

    public function mount()
    {
        $transaction = new PaymentTransactions();
        $this->transactions = $transaction->sumTodaySales();
        $this->count_transactions = $transaction->countTodaysTransaction();

        //User Information on Dashboard
        $this->users = User::userCountFormatted(); // Call the static method directly on the User model
        $this->complaints = ContactUs::userComplains(); // Call the static method directly on the ContactUs model


    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}

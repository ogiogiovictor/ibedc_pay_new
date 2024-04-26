<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Transactions\PayTransactions;
use App\Enums\RoleEnum;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Livewire\AuthorizeTransactions;

class LogTransactions extends Component
{
    use WithPagination;

    public $transactions;
    public $clearOption;
    public $clearValue;

    public function mount()
    {

        $user = Auth::user();

        $this->transactions = AuthorizeTransactions::authorizeTransaction($user);
       
        // if($this->authorize(RoleEnum::super_admin()->value)){
        //     $transaction = new PayTransactions();
        //     $this->transactions = $transaction->ibedcpayTransactions();
        // } else if($this->authorize(RoleEnum::admin()->value)) {
        //     $transaction = new PayTransactions();
        //     $this->transactions = $transaction->ibedcpayTransactions();
        // }else if($this->authorize(RoleEnum::manager()->value)) {
        //     $transaction = new PayTransactions();
        //     $this->transactions = $transaction->agencyTransaction($user->agency);
        // }else if($this->authorize(RoleEnum::supervisor()->value)) {
        //     $transaction = new PayTransactions();
        //     $this->transactions = $transaction->agencyTransaction($user->agency);
        // } else {
        //     abort(403);
        // }

        
       

    }

    public function searchTransactions()
    {

        if (!$this->clearOption) {
            session()->flash('error', 'Please select an option');
            return;
        }


        if ($this->clearOption && $this->clearValue) {
            $option = $this->clearOption;
            $value = $this->clearValue;
        }

        
        $this->transactions = PayTransactions::query()
            ->when($this->clearOption && $this->clearValue, function ($query) {
                $query->where($this->clearOption, '=', $this->clearValue);
            })
           ->orderByDesc('created_at') // Assuming 'created_at' is the column you want to order by
            ->get();

        if ($this->transactions->isEmpty()) {
            $this->transactions = collect();
        }

    }

    public function exportTransactions() {
        
    }

    public function render()
    {

    

        return view('livewire.log-transactions');
    }
}

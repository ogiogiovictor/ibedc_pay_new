<?php

namespace App\Livewire;

use Livewire\Component;

use App\Models\Wallet\WalletHistory;
use App\Models\Wallet\WalletUser;
use App\Models\User;
use App\Models\VirtualAccount;
use App\Models\VirtualAccountTrasactions;
use App\Models\Transactions\PaymentTransactions;
use Livewire\WithPagination;

class UserVirtualAccount extends Component
{
    use WithPagination;
    public $vaccounts;
    public $clearOption;
    public $clearValue;

    public function mount() {

        $account = new VirtualAccount();
        $this->vaccounts = $account->orderBy('created_at', 'desc')->paginate(50)->toArray();
       
    }


    
    public function searchUserAccount() {

        if (!$this->clearOption) {
            session()->flash('error', 'Please select an option');
            return;
        }


        if ($this->clearOption && $this->clearValue) {
            $option = $this->clearOption;
            $value = $this->clearValue;
        }

      
        $this->vaccounts = VirtualAccount::query()
        ->when($this->clearOption && $this->clearValue, function ($query) {
            $query->where($this->clearOption, 'like', '%' . $this->clearValue . '%');
        })
       ->orderByDesc('created_at') // Assuming 'created_at' is the column you want to order by
        ->paginate(10)->toArray();

       // dd($this->vaccounts);

       

        // if ($this->vaccounts->isEmpty()) {
        //     $this->vaccounts = collect();
        // }
 
     }


    public function render()
    {
        return view('livewire.user-virtual-account');
    }
}

<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transactions\PaymentTransactions;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use DB;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Agency\Targets;


class AgencyDashboard extends Component
{

    use WithPagination;

    public $user = [];
    public $total_users; 
    public $users_registered_today;
    public $transactions;
    public $transaction_sum;
    public $target;
    public $transaction_count;

    public function mount() {

        $this->user = Auth::user();

         // Get the current year and month
         $this->current_year = Carbon::now()->year;
         $this->current_month = Carbon::now()->month;

        $this->total_users = User::where('agency', $this->user->agency)->count();
        $this->users_registered_today = User::where('agency', $this->user->agency)->whereDate('created_at', Carbon::today())->count();
        $this->transactions = PaymentTransactions::where('agency', $this->user->agency)->paginate(20)->toArray();
        $this->transaction_count = PaymentTransactions::where('agency', $this->user->agency)->whereIn('status', ['processing', 'success'])->count();
        $this->transaction_sum = PaymentTransactions::where('agency', $this->user->agency)
                            ->whereIn('status', ['processing', 'success'])
                            ->selectRaw('SUM(CAST(amount AS DECIMAL(18, 2))) as total_amount')
                            ->value('total_amount');

        // Calculating the sum for the current year and current month
        $this->target = Targets::where('agency_id', $this->user->agency)
            ->where('year', $this->current_year)
            ->where('month', $this->current_month)
            ->value("target_amount");

    }


    public function render()
    {
        return view('livewire.agency-dashboard');
    }
}

<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transactions\PaymentTransactions;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use DB;


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
    public $pendingCollection = "";


    public function mount() {

        $user = Auth::user();
        $this->transactions = PaymentTransactions::where(["agency" => $this->id, 'agency' => $user->agency])->orderby("created_at", "desc")->paginate(10)->toArray();
        $this->totalCollection = PaymentTransactions::whereIn("status", ['processing', 'success'])->where(["agency" => $this->id, 'agency' => $user->agency])->sum(DB::raw('CONVERT(decimal(18,2), amount)'));

        $startDate = now()->startOfMonth()->toDateString();
        $endDate = now()->endOfMonth()->toDateString();
    
        $this->monthlyCollection = PaymentTransactions::whereBetween('created_at', [$startDate, $endDate])
                ->where(["agency" => $this->id, 'agency' => $user->agency])
                ->whereIn('status', ['processing', 'success'])
                ->whereNotNull('providerRef')
                ->select(DB::raw('CAST(SUM(CAST(amount AS DECIMAL(18, 2))) AS DECIMAL(18, 2)) AS sum_amount'))
                ->first()
                ->sum_amount;

        $today = now()->toDateString();
    
        $this->todaysCollection = PaymentTransactions::whereDate('created_at', $today)
                ->whereIn('status', ['processing', 'success'])
                ->where(["agency" => $this->id, 'agency' => $user->agency])
                ->whereNotNull('providerRef')
                ->select(DB::raw('CAST(SUM(CAST(amount AS DECIMAL(10, 2))) AS DECIMAL(10, 2)) AS sum_amount'))
                ->first()
                ->sum_amount;

        $this->pendingCollection = PaymentTransactions::whereIn("status", ['processing'])
        ->whereNotNull('providerRef')
        ->where(["agency" => $this->id, 'agency' => $user->agency])->sum(DB::raw('CONVERT(decimal(18,2), amount)'));

    }


    public function render()
    {

        return view('livewire.agenct-transactions');
    }
}

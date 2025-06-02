<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transactions\PaymentTransactions;
use App\Models\User;
use App\Models\ContactUs;
use App\Models\Transactions\PayTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Enums\RoleEnum;

class Dashboard extends Component
{

    public $transactions;
    public $count_transactions;
    public $users;
    public $complaints;
    public $all_transactions;
    public $today_transactions;
    public $monthlyCollection;
    public $totalCollection;
    public $clearOption;
    public $clearValue;

    public function mount()
    {

        $user = Auth::user();

        if($user->authority == (RoleEnum::agency_admin()->value )) {
          //redirect to agency dashboard
          return redirect()->route('agency_dashboard');
        } 

        if($user->authority == (RoleEnum::user()->value)  || $user->authority == (RoleEnum::supervisor()->value)
        
        || $user->authority == (RoleEnum::bhm()->value) || $user->authority == (RoleEnum::dtm()->value)
        || $user->authority == (RoleEnum::region()->value) || $user->authority == (RoleEnum::billing()->value)
        ) {
           abort(403, 'Unathorized action. You do not have access to this page');
        } 

        $today = now()->toDateString();
        $startDate = now()->startOfMonth()->toDateString();
        $endDate = now()->endOfMonth()->toDateString();


        $transaction = new PaymentTransactions();
        $this->transactions = $transaction->sumTodaySales();

        /////////////// TODAY'S COLLECTION ///////////////////////
        $today_ibedcv2 = $transaction->whereDate('created_at', $today)
        ->whereIn('status', ['success', 'processing'])
        ->whereNotNull('providerRef')
        ->select(DB::raw('CAST(SUM(CAST(amount AS DECIMAL(10, 2))) AS DECIMAL(10, 2)) AS sum_amount'))
        ->first()
        ->sum_amount;

        /////////////// MONTHLY COLLECTION ///////////////////////
        $monthlyCollectionv2 = $transaction->whereBetween('created_at', [$startDate, $endDate])
        ->whereIn('status', ['success', 'processing'])
        ->whereNotNull('providerRef')
        ->select(DB::raw('CAST(SUM(CAST(amount AS DECIMAL(18, 2))) AS DECIMAL(18, 2)) AS sum_amount'))
        ->first()
        ->sum_amount;

        //////////////// TOTAL IBEDC COLLECTION /////////////////////////////////////
        $totalCollectionv2 = $transaction->whereIn('status', ['success', 'processing'])
            ->whereNotNull('providerRef')
            ->select(DB::raw('CAST(SUM(CAST(amount AS DECIMAL(18, 2))) AS DECIMAL(18, 2)) AS sum_amount'))
            ->first()
            ->sum_amount;


 /////////////////////////////////////////////////////////// IBEDC VERSION 1//////////////////////////////////////////////////////V1ibedc Pay
        $today_ibedcv1 = PayTransactions::whereDate('created_at', $today)
        ->whereIn('status', ['pending', 'success'])
        ->whereNotNull('providerRef')
        ->select(DB::raw('CAST(SUM(CAST(amount AS DECIMAL(10, 2))) AS DECIMAL(10, 2)) AS sum_amount'))
        ->first()
        ->sum_amount;

        $monthlyCollectionv1 = PayTransactions::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['pending', 'success'])
            ->whereNotNull('providerRef')
            ->select(DB::raw('CAST(SUM(CAST(amount AS DECIMAL(18, 2))) AS DECIMAL(18, 2)) AS sum_amount'))
            ->first()
            ->sum_amount;

        
        $totalCollectionv1 = PayTransactions::whereIn('status', ['pending', 'success'])
            ->whereNotNull('providerRef')
            ->select(DB::raw('CAST(SUM(CAST(amount AS DECIMAL(18, 2))) AS DECIMAL(18, 2)) AS sum_amount'))
            ->first()
            ->sum_amount;

        $this->today_transactions =  $today_ibedcv2 + $today_ibedcv1;

        $this->monthlyCollection = $monthlyCollectionv2 +  $monthlyCollectionv1;

        $this->totalCollection  =  $totalCollectionv2 + $totalCollectionv1;

        //User Information on Dashboard
        $this->users = User::userCountFormatted(); // Call the static method directly on the User model
        $this->complaints = ContactUs::userComplains(); // Call the static method directly on the ContactUs model

        //All Transactions
        $this->all_transactions = $transaction->whereIn('status', ['processing', 'failed', 'started'])->orderby('created_at', 'desc')->limit(50)->get();

        


    }


    public function searchTransactions() {

        if (!$this->clearOption) {
            session()->flash('error', 'Please select an option');
            return;
        }


        if ($this->clearOption && $this->clearValue) {
            $option = $this->clearOption;
            $value = $this->clearValue;
        }

        $this->all_transactions = PaymentTransactions::query()
        ->when($this->clearOption && $this->clearValue, function ($query) {
            $query->where($this->clearOption, '=', $this->clearValue);
        })
       ->orderByDesc('created_at') // Assuming 'created_at' is the column you want to order by
        ->get();

        if ($this->all_transactions->isEmpty()) {
            $this->all_transactions = collect();
        }


    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}

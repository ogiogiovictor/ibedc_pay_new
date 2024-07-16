<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Transactions\PayTransactions;
use App\Enums\RoleEnum;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Livewire\AuthorizeTransactions;
use Illuminate\Support\Facades\DB;

class LogTransactions extends Component
{
    use WithPagination;

    public $transactions = [];
    public $clearOption;
    public $clearValue;
    public $monthlyCollection;
    public $today;
    public $totalCollection;

    public function mount()
    {
        $user = Auth::user();

        if($user->authority == (RoleEnum::agency_admin()->value )) {
          //redirect to agency dashboard
          return redirect()->route('agency_dashboard');
        } 
       
        $this->loadData();

    }

    public function loadData(){

        try {
            $user = Auth::user();
            $today = now()->toDateString();
    
            $this->transactions = AuthorizeTransactions::authorizeTransaction($user);
    
            $this->today = PayTransactions::whereDate('created_at', $today)
                ->whereIn('status', ['pending', 'success'])
                ->whereNotNull('providerRef')
                ->select(DB::raw('CAST(SUM(CAST(amount AS DECIMAL(10, 2))) AS DECIMAL(10, 2)) AS sum_amount'))
                ->first()
                ->sum_amount;
    
            $startDate = now()->startOfMonth()->toDateString();
            $endDate = now()->endOfMonth()->toDateString();
    
            $this->monthlyCollection = PayTransactions::whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('status', ['pending', 'success'])
                ->whereNotNull('providerRef')
                ->select(DB::raw('CAST(SUM(CAST(amount AS DECIMAL(18, 2))) AS DECIMAL(18, 2)) AS sum_amount'))
                ->first()
                ->sum_amount;
    
            $this->monthlyCollection = (float) $this->monthlyCollection;
    
            $this->totalCollection = PayTransactions::whereIn('status', ['pending', 'success'])
                ->whereNotNull('providerRef')
                ->select(DB::raw('CAST(SUM(CAST(amount AS DECIMAL(18, 2))) AS DECIMAL(18, 2)) AS sum_amount'))
                ->first()
                ->sum_amount;
    
            $this->totalCollection = (float) $this->totalCollection;
        } catch (\Exception $e) {
            // Log or handle the exception appropriately
            logger()->error('Error loading data: ' . $e->getMessage());
        }
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

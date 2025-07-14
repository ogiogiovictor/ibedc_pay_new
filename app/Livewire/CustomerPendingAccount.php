<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\NAC\AccoutCreaction;
use App\Models\NAC\UploadAccountCreation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Enums\RoleEnum;
use App\Models\NAC\UploadHouses;
use Carbon\Carbon;


// for status 0 =  customer 1 = dtm, 2 = billing 3 = rejected, 4 = completed

class CustomerPendingAccount extends Component
{

    public $customers;
    public $accounts;
    public $totalAccount;
    public $pendingApproval;
    public $clearOption;
    public $clearValue;
    public $fromdate;
    public $todate;
    // public $completedAccounts;


    public function mount()
    {

        $user = Auth::user();

        if($user->authority == (RoleEnum::agency_admin()->value )) {
          //redirect to agency dashboard
          return redirect()->route('agency_dashboard');
        } 

        if($user->authority == (RoleEnum::user()->value)  || $user->authority == (RoleEnum::supervisor()->value) ) {
           abort(403, 'Unathorized action.');
        } 

      // $query = UploadHouses::query();
       $query = UploadHouses::with('customer'); 

        if ($user->authority == RoleEnum::super_admin()->value) {
            // Super Admin sees all
            $this->accounts = $query->orderBy('id', 'desc')->get();
            $this->totalAccount = UploadHouses::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)->whereIn('status', ['2'])->count();

        } elseif ($user->authority == RoleEnum::dtm()->value) {    
            // DTM must have both region and business hub
            if (empty($user->region) || empty($user->business_hub)) {
                $this->accounts = collect(); // empty collection
            } else {
            
                // Base query
                $baseQuery = UploadHouses::with('customer')
                    ->where('region', $user->region)
                    ->where('business_hub', $user->business_hub)
                    ->where('service_center', $user->sc);

                // Get pending accounts
                $this->accounts = (clone $baseQuery)
                    ->whereIn('status', ['1'])
                    ->orderBy('id', 'desc')
                    ->get();

                // Total completed accounts
                $this->totalAccount = (clone $baseQuery)
                    ->whereIn('status', ['4'])
                    ->count();

                // Pending approval
                $this->pendingApproval = (clone $baseQuery)
                    ->where('status', '1')
                    ->count();

            }
        } elseif ($user->authority == RoleEnum::bhm()->value) {

            $baseQuery = UploadHouses::with('customer')
        ->where('region', $user->region)
        ->where('business_hub', $user->business_hub);

            $this->accounts = (clone $baseQuery)
                ->whereIn('status', ['1'])
                ->orderBy('id', 'desc')
                ->get();

            $this->totalAccount = (clone $baseQuery)
                ->whereIn('status', ['4'])
                ->count();

            $this->pendingApproval = (clone $baseQuery)
                ->whereIn('status', ['0'])
                ->count();
            
            
        } elseif ($user->authority == RoleEnum::billing()->value) {

           $baseQuery = UploadHouses::with('customer');

            $this->accounts = (clone $baseQuery)
                ->whereIn('status', ['2'])
                ->orderBy('id', 'desc')
                ->get();

            $this->totalAccount = (clone $baseQuery)
                ->whereIn('status', ['4'])
                ->count();

            $this->pendingApproval = (clone $baseQuery)
                ->whereIn('status', ['1', '2', '0'])
                ->count();

            
        } elseif ($user->authority == RoleEnum::rico()->value) {

           $baseQuery = UploadHouses::with('customer');

            $this->accounts = (clone $baseQuery)
                ->whereIn('status', ['2', '4', '1'])
                ->orderBy('id', 'desc')
                ->get();

            $this->totalAccount = (clone $baseQuery)
                ->whereIn('status', ['4'])
                ->count();

            $this->pendingApproval = (clone $baseQuery)
                ->whereIn('status', ['1', '2', '0'])
                ->count();

            
        }  elseif ($user->authority == RoleEnum::audit()->value) {

           $baseQuery = UploadHouses::with('customer');

            $this->accounts = (clone $baseQuery)
                ->whereIn('status', ['2', '4', '1'])
                ->orderBy('id', 'desc')
                ->get();

            $this->totalAccount = (clone $baseQuery)
                ->whereIn('status', ['4'])
                ->count();

            $this->pendingApproval = (clone $baseQuery)
                ->whereIn('status', ['1', '2', '0'])
                ->count();

            
        } elseif ($user->authority == RoleEnum::mso()->value) {

           $baseQuery = UploadHouses::with('customer');

            $this->accounts = (clone $baseQuery)
                ->where('evaluated', 'no')
                ->orderBy('id', 'desc')
                ->get();

            $this->totalAccount = (clone $baseQuery)
                ->whereIn('status', ['4'])->where('evaluated', 'yes')
                ->count();

            $this->pendingApproval = (clone $baseQuery)
                ->whereIn('status', ['1'])->where('evaluated', 'yes')
                ->count();

            
        }else {
            $this->accounts = collect(); // fallback empty
        }

    }

    private function baseQueryForUser($user)
    {
        return UploadHouses::with('customer')
            ->where('region', $user->region)
            ->where('business_hub', $user->business_hub)
            ->when(isset($user->sc), fn($q) => $q->where('service_center', $user->sc));
    }

    public function searchTransactions(){
       
    
        // Validate that if either is filled, both must be filled
    if ((empty($this->clearOption) && !empty($this->clearValue)) || (!empty($this->clearOption) && empty($this->clearValue))) {
        session()->flash('error', 'Both "Select" and "Enter Value" fields are required for search.');
        return;
    }


    $user = Auth::user();
    $query = UploadHouses::with('customer');

    // Region-based filter
    if ($user->authority === RoleEnum::dtm()->value) {
        $query->where('region', $user->region)
              ->where('business_hub', $user->business_hub)
              ->where('service_center', $user->sc);
    } elseif ($user->authority === RoleEnum::bhm()->value) {
        $query->where('region', $user->region)
              ->where('business_hub', $user->business_hub);
    }


    // Apply search filter
    if (!empty($this->clearOption) && !empty($this->clearValue)) {
        $query->where($this->clearOption, 'like', '%' . $this->clearValue . '%');
    }
    $this->accounts = $query->orderBy('id', 'desc')->get();
    }



    public function exportTransactions()
{
    if (empty($this->fromdate) || empty($this->todate)) {
        session()->flash('error', 'Please select a date range first.');
        return;
    }

    $user = Auth::user();

    $query = UploadHouses::with('customer');

    // Role-based filter
    if ($user->authority === RoleEnum::dtm()->value) {
        $query->where('region', $user->region)
              ->where('business_hub', $user->business_hub)
              ->where('service_center', $user->sc)
              ->whereIn('status', ['0']);
    } elseif ($user->authority === RoleEnum::bhm()->value) {
        $query->where('region', $user->region)
              ->where('business_hub', $user->business_hub)
              ->whereIn('status', ['1']);
    } elseif ($user->authority === RoleEnum::billing()->value) {
        $query->whereIn('status', ['2']);
    } elseif ($user->authority === RoleEnum::mso()->value) {
        $query->where('evaluated', 'no');
    }

    // Apply date filter (based on created_at)
    $query->whereBetween('created_at', [
        Carbon::parse($this->fromdate)->startOfDay(),
        Carbon::parse($this->todate)->endOfDay()
    ]);

    $accounts = $query->orderBy('id', 'desc')->get();

    if ($accounts->isEmpty()) {
        session()->flash('error', 'No data available for the selected date range.');
        return;
    }

    $filename = 'accounts_export_' . now()->format('Ymd_His') . '.csv';
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
    ];

    $columns = [
        'Applied Date', 'Tracking ID', 'Customer Name', 'Region',
        'Business Hub', 'Service Center', 'Status', 'Account Number', 'Date Past'
    ];

    $callback = function () use ($columns, $accounts) {
        $file = fopen('php://output', 'w');
        fputcsv($file, $columns);

        foreach ($accounts as $item) {
            $customerName = trim(($item->customer->surname ?? '') . ' ' .
                                 ($item->customer->firstname ?? '') . ' ' .
                                 ($item->customer->other_name ?? ''));

            $statusText = match ($item->status) {
                '0' => 'Started',
                '1' => 'Processing',
                '2' => 'Completed',
                '3' => 'Rejected',
                '4' => 'Ongoing',
                default => 'N/A',
            };

            $datePast = $item->status == '0'
                ? $item->created_at->diffForHumans()
                : $item->updated_at->diffForHumans();

            fputcsv($file, [
                $item->created_at,
                $item->tracking_id,
                $customerName,
                $item->region,
                $item->business_hub,
                $item->service_center,
                $statusText,
                $item->account_no,
                $datePast,
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}



    public function render()
    {
        return view('livewire.customer-pending-account');
    }
}

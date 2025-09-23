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
    public $completed;
    public $started;
    public $pending;
    public $withbilling;
    public $withcompliance;
    public $rejected;


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
            $this->accounts = $query->orderBy('id', 'desc')->paginate(30)->toArray();
            $this->totalAccount = UploadHouses::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)->whereIn('status', ['2'])->count();

             $baseQuery = UploadHouses::with('customer');

             $this->started = (clone $baseQuery)
                ->whereIn('status', ['0'])
                ->count();

            $this->pending = (clone $baseQuery)
                ->whereIn('status', ['1'])
                ->count();

            $this->completed = (clone $baseQuery)
                ->whereIn('status', ['4'])
                ->count();

            $this->withbilling = (clone $baseQuery)
                ->whereIn('status', ['2'])
                ->count();

             $this->withcompliance = (clone $baseQuery)
                ->whereIn('status', ['3'])
                ->count();

             $this->rejected = (clone $baseQuery)
                ->whereIn('status', ['5'])
                ->count();

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
                    ->paginate(30)->toArray();

                // Total completed accounts
                $this->totalAccount = (clone $baseQuery)
                    ->whereIn('status', ['4'])
                    ->count();

                // Pending approval
                $this->pendingApproval = (clone $baseQuery)
                    ->where('status', '1')
                    ->count();

                 $this->withcompliance = (clone $baseQuery)
                ->whereIn('status', ['3'])
                ->count();

                 $this->withbilling = (clone $baseQuery)
                ->whereIn('status', ['2'])
                ->count();

                $this->rejected = (clone $baseQuery)
                ->whereIn('status', ['5'])
                ->count();

            }
        } elseif ($user->authority == RoleEnum::bhm()->value) {

            $baseQuery = UploadHouses::with('customer')
            ->where('region', $user->region)
            ->where('business_hub', $user->business_hub);

            $this->accounts = (clone $baseQuery)
                ->whereIn('status', ['1'])
                ->orderBy('id', 'desc')
                ->paginate(30)->toArray();

            $this->totalAccount = (clone $baseQuery)
                ->whereIn('status', ['4'])
                ->count();

            $this->pendingApproval = (clone $baseQuery)
                ->whereIn('status', ['0'])
                ->count();
            
             $this->withbilling = (clone $baseQuery)
                ->whereIn('status', ['2'])
                ->count();

            $this->rejected = (clone $baseQuery)
                ->whereIn('status', ['5'])
                ->count();

             $this->withcompliance = (clone $baseQuery)
                ->whereIn('status', ['3'])
                ->count();
            
            
        } elseif ($user->authority == RoleEnum::billing()->value) {

           $baseQuery = UploadHouses::with('customer');

            if($user->region == "HQ"){

                $this->accounts = (clone $baseQuery)
                ->whereIn('status', ['2'])
                ->orderBy('id', 'desc')
                ->paginate(30)->toArray();

                $this->totalAccount = (clone $baseQuery)
                ->whereIn('status', ['4'])
                ->count();

                $this->pending = (clone $baseQuery)
                    ->whereIn('status', ['1', '2', '3'])
                    ->count();

                 $this->withcompliance = (clone $baseQuery)
                ->whereIn('status', ['3'])
                ->count();


                $this->pendingApproval = (clone $baseQuery)
                    ->whereIn('status', ['1', '2', '0'])
                    ->count();

                $this->withbilling = (clone $baseQuery)
                    ->whereIn('status', ['2'])
                    ->count();

                $this->rejected = (clone $baseQuery)
                    ->whereIn('status', ['5'])
                    ->count();


           } else {

                $this->accounts = (clone $baseQuery)
                    ->where('region', $user->region)
                    ->whereIn('status', ['2'])
                    ->orderBy('id', 'desc')
                    ->paginate(30)->toArray();

                $this->totalAccount = (clone $baseQuery)
                 ->where('region', $user->region)
                ->whereIn('status', ['4'])
                ->count();

                $this->pending = (clone $baseQuery)
                 ->where('region', $user->region)
                    ->whereIn('status', ['1', '2', '3'])
                    ->count();

                 $this->withcompliance = (clone $baseQuery)
                ->whereIn('status', ['3'])
                ->count();


                $this->pendingApproval = (clone $baseQuery)
                 ->where('region', $user->region)
                    ->whereIn('status', ['1', '2', '0'])
                    ->count();

                $this->withbilling = (clone $baseQuery)
                 ->where('region', $user->region)
                    ->whereIn('status', ['2'])
                    ->count();

                $this->rejected = (clone $baseQuery)
                 ->where('region', $user->region)
                    ->whereIn('status', ['5'])
                    ->count();

           }

           

            
            
        } elseif ($user->authority == RoleEnum::rico()->value) {

           $baseQuery = UploadHouses::with('customer');

           if($user->region == "HQ"){

                $this->accounts = (clone $baseQuery)
                    ->whereIn('status', ['2', '4', '1', '0'])
                    ->orderBy('id', 'desc')
                    ->paginate(30)->toArray();

                $this->totalAccount = (clone $baseQuery)
                ->whereIn('status', ['4'])
                ->count();

                $this->started = (clone $baseQuery)
                    ->whereIn('status', ['0'])
                    ->count();

                $this->pending = (clone $baseQuery)
                    ->whereIn('status', ['1', '2'])
                    ->count();

                 $this->withcompliance = (clone $baseQuery)
                ->whereIn('status', ['3'])
                ->count();

                $this->completed = (clone $baseQuery)
                    ->whereIn('status', ['4'])
                    ->count();

                $this->withbilling = (clone $baseQuery)
                    ->whereIn('status', ['2'])
                    ->count();

                $this->rejected = (clone $baseQuery)
                    ->whereIn('status', ['5'])
                    ->count();

           } else {

             $this->accounts = (clone $baseQuery)
                ->where('region', $user->region)
                ->whereIn('status', ['2', '4', '1', '0'])
                ->orderBy('id', 'desc')
                ->paginate(30)->toArray();

                $this->totalAccount = (clone $baseQuery)
                 ->where('region', $user->region)
                ->whereIn('status', ['4'])
                ->count();

                 $this->withcompliance = (clone $baseQuery)
                ->whereIn('status', ['3'])
                ->count();

                $this->started = (clone $baseQuery)
                 ->where('region', $user->region)
                    ->whereIn('status', ['0'])
                    ->count();

                $this->pending = (clone $baseQuery)
                 ->where('region', $user->region)
                    ->whereIn('status', ['1', '2'])
                    ->count();

                $this->completed = (clone $baseQuery)
                 ->where('region', $user->region)
                    ->whereIn('status', ['4'])
                    ->count();

                $this->withbilling = (clone $baseQuery)
                 ->where('region', $user->region)
                    ->whereIn('status', ['2'])
                    ->count();

                $this->rejected = (clone $baseQuery)
                 ->where('region', $user->region)
                    ->whereIn('status', ['5'])
                    ->count();
           }

           

            

            
        }  elseif ($user->authority == RoleEnum::audit()->value) {

           $baseQuery = UploadHouses::with('customer');

          if($user->region == "HQ"){
               $this->accounts = $query->orderBy('id', 'desc')->paginate(30)->toArray();

                $this->totalAccount = (clone $baseQuery)
                ->whereIn('status', ['4'])
                ->count();

                $this->pendingApproval = (clone $baseQuery)
                    ->whereIn('status', ['1', '2', '0'])
                    ->count();

                $this->withbilling = (clone $baseQuery)
                    ->whereIn('status', ['2'])
                    ->count();

                 $this->withcompliance = (clone $baseQuery)
                ->whereIn('status', ['3'])
                ->count();

                $this->pending = (clone $baseQuery)
                    ->whereIn('status', ['1', '2'])
                    ->count();

                $this->completed = (clone $baseQuery)
                    ->whereIn('status', ['4'])
                    ->count();


                $this->started = (clone $baseQuery)
                    ->whereIn('status', ['0'])
                    ->count();

                $this->rejected = (clone $baseQuery)
                    ->whereIn('status', ['5'])
                    ->count();


           } else {

                $this->accounts = (clone $baseQuery)
                    ->where('region', $user->region)
                    ->whereIn('status', ['2', '4', '1', '0'])
                    ->orderBy('id', 'desc')
                    ->paginate(30)->toArray();

                    $this->totalAccount = (clone $baseQuery)
                     ->where('region', $user->region)
                    ->whereIn('status', ['4'])
                    ->count();

                $this->pendingApproval = (clone $baseQuery)
                 ->where('region', $user->region)
                    ->whereIn('status', ['1', '2', '0'])
                    ->count();

                $this->withbilling = (clone $baseQuery)
                 ->where('region', $user->region)
                    ->whereIn('status', ['2'])
                    ->count();

                 $this->withcompliance = (clone $baseQuery)
                ->whereIn('status', ['3'])
                ->count();

                $this->pending = (clone $baseQuery)
                 ->where('region', $user->region)
                    ->whereIn('status', ['1', '2'])
                    ->count();

                $this->completed = (clone $baseQuery)
                 ->where('region', $user->region)
                    ->whereIn('status', ['4'])
                    ->count();


                $this->started = (clone $baseQuery)
                 ->where('region', $user->region)
                    ->whereIn('status', ['0'])
                    ->count();

                $this->rejected = (clone $baseQuery)
                 ->where('region', $user->region)
                    ->whereIn('status', ['5'])
                    ->count();

           }


           
            
        } elseif ($user->authority == RoleEnum::mso()->value) {

           $baseQuery = UploadHouses::with('customer');

            $this->accounts = (clone $baseQuery)
                ->where('evaluated', 'no')
                ->orderBy('id', 'desc')
                ->paginate(30);

            $this->totalAccount = (clone $baseQuery)
                ->whereIn('status', ['4'])->where('evaluated', 'yes')
                ->count();

            $this->pendingApproval = (clone $baseQuery)
                ->whereIn('status', ['1'])->where('evaluated', 'yes')
                ->count();

                 $this->withcompliance = (clone $baseQuery)
                ->whereIn('status', ['3'])
                ->count();

             $this->rejected = (clone $baseQuery)
                ->whereIn('status', ['5'])
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

    // Status mapping
    $statusMap = [
        'started'     => [0],
        'with dtm'    => [1],
        'with billing'=> [2],
        'with compliance'=> [3],
        'completed'   => [4],
        'rejected'    => [5], // adjust if 6 or 5 is correct in your DB
    ];


    // Apply search filter
    if (!empty($this->clearOption) && !empty($this->clearValue)) {
     //   $query->where($this->clearOption, 'like', '%' . $this->clearValue . '%');
       if ($this->clearOption === "surname") {
            // Step 1: find matching customer IDs from AccountCreation
            $customerIds = AccoutCreaction::where(function ($q) {
                    $q->where('surname', 'like', '%' . $this->clearValue . '%')
                      ->orWhere('firstname', 'like', '%' . $this->clearValue . '%')
                      ->orWhere('other_name', 'like', '%' . $this->clearValue . '%');
                })
                ->pluck('id'); // just get the IDs

            // Step 2: filter UploadHouses by those customer IDs
            $query->whereIn('customer_id', $customerIds);
        } elseif ($this->clearOption === "status") {
            $statusKey = strtolower(trim($this->clearValue));
            if (array_key_exists($statusKey, $statusMap)) {
                $query->whereIn('status', $statusMap[$statusKey]);
            }
        } else {
            // Normal filter (direct column match)
            $query->where($this->clearOption, 'like', '%' . $this->clearValue . '%');
        }

    }
   // $this->accounts = $query->orderBy('id', 'desc')->get();

     $this->accounts = $query->orderBy('id', 'desc')->paginate(30)->toArray();

   

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
        'Business Hub', 'Service Center', 'Status', 'Account Number', 'Date Past', 'house_no', 'full_address', 'nearest_bustop', 'lga', 'landmark', 'type_of_premise', 'use_of_premise'
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
                '1' => 'Processing/With DTM',
                '2' => 'With Billing',
                '5' => 'Rejected',
                '4' => 'Completed',
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
                $item->house_no,
                $item->full_address,
                $item->nearest_bustop,
                $item->lga,
                $item->landmark,
                $item->type_of_premise,
                $item->use_of_premise,
                
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

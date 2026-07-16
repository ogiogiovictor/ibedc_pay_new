<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\NAC\AccoutCreaction;
use App\Models\NAC\UploadAccountCreation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Enums\RoleEnum;
use App\Models\NAC\UploadHouses;

class NewAccount extends Component
{

    public $customers;
    public $totalSubmitted;
    public $submittedToday;
    public $submittedThisMonth;
    public $completedAccounts;
     public $totalCustomers;
    public $clearOption = '';
    public $clearValue = '';

    public function mount()
    {

        $user = Auth::user();

        if($user->authority == (RoleEnum::agency_admin()->value )) {
          //redirect to agency dashboard
          return redirect()->route('agency_dashboard');
        } 

        if($user->authority == (RoleEnum::user()->value)  || $user->authority == (RoleEnum::supervisor()->value) ) {
           abort(403, 'Unathorized action. No Access Allowed');
        } 

         $customers = new AccoutCreaction();

          $this->customers = $customers
          ->with(['continuation', 'uploadinformation', 'caccounts', 'uploadedPictures'])->withCount('uploadedPictures')
          ->whereIn('status', ['started', 'processing', 'with-dtm', 'with-bhm', 'with-billing', 'completed']);

            // Completed accounts (status = 'completed')
            $this->completedAccounts = AccoutCreaction::where('status', 'completed')->count();

            // Apply region and business_hub filters based on role
            if ($user->authority == RoleEnum::super_admin()->value) {
                // No filtering – super admin and billing see everything
                 $this->customers = $this->customers->orderBy('created_at', 'desc')->paginate(30)->toArray();

                 $this->submittedThisMonth = UploadHouses::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)->where('status', 0)
                ->count();

                $this->totalCustomers = AccoutCreaction::count();


                //  $this->submittedToday = AccoutCreaction::whereMonth('created_at', Carbon::now()->month)
                //     ->whereYear('created_at', Carbon::now()->year)->whereIn('status', ['started', 'processing', 'with-dtm', 'with-bhm', 'with-billing'])->count();

            } elseif ($user->authority == RoleEnum::dtm()->value) {
                // Filter by region only
                // If region is missing, return empty
                 // If either region or business_hub is missing, return empty
                    if (empty($user->region) || empty($user->business_hub)) {
                        $this->customers = collect(); // empty collection
                    } else {
                        $this->customers = $this->customers->orderBy('created_at', 'desc')->where('region', $user->region)->whereIn('status', ['processing', 'with-dtm'])->get();

                        $this->submittedThisMonth = UploadHouses::whereMonth('created_at', Carbon::now()->month)
                        ->whereYear('created_at', Carbon::now()->year)->where('status', 0)->where('region', $user->region)
                        ->count();

                        $this->totalCustomers = AccoutCreaction::count();

                        //$this->submittedToday = AccoutCreaction::whereMonth('created_at', Carbon::now()->month)->where('region', $user->region)->whereYear('created_at', Carbon::now()->year)->whereIn('status', ['started', 'processing', 'with-dtm', 'with-bhm', 'with-billing'])->count();

                    }

            } elseif ($user->authority == RoleEnum::view_only()->value) {
                // Filter by region only
                // If region is missing, return empty
                 // If either region or business_hub is missing, return empty
                  if ($user->region == "HQ") {
                    //$this->customers = collect(); // empty collection
                    $this->customers = $this->customers->orderBy('created_at', 'desc')->paginate(30)->toArray();
                    $this->totalCustomers = AccoutCreaction::count();
                    
                    }else if (empty($user->region) || empty($user->business_hub)) {
                       // $this->customers = collect(); // empty collection
                          $this->customers = $this->customers->orderBy('created_at', 'desc')->paginate(30)->toArray();
                         $this->totalCustomers = AccoutCreaction::count();

                    } else {
                        $this->customers = $this->customers->orderBy('created_at', 'desc')->where('region', $user->region)->whereIn('status', ['processing', 'with-dtm'])->paginate(30)
                      ->toArray();;

                        $this->submittedThisMonth = UploadHouses::whereMonth('created_at', Carbon::now()->month)
                        ->whereYear('created_at', Carbon::now()->year)->where('status', 0)->where('region', $user->region)
                        ->count();

                        $this->totalCustomers = AccoutCreaction::count();

                        //$this->submittedToday = AccoutCreaction::whereMonth('created_at', Carbon::now()->month)->where('region', $user->region)->whereYear('created_at', Carbon::now()->year)->whereIn('status', ['started', 'processing', 'with-dtm', 'with-bhm', 'with-billing'])->count();

                    }

            } elseif ($user->authority == RoleEnum::bhm()->value || $user->authority == RoleEnum::mso()->value) {
                // Filter by region and business hub
               // If either region or business_hub is missing, return empty
                if (empty($user->region) || empty($user->business_hub)) {
                    $this->customers = collect(); // empty collection
                } else {
                    $this->customers = $this->customers->where('region', $user->region)
                    ->whereIn('status', ['with-dtm', 'with-bhm'])->get();

                        $this->totalCustomers = AccoutCreaction::count();

                        //  $this->submittedToday = AccoutCreaction::whereMonth('created_at', Carbon::now()->month)->where('region', $user->region)
                        //  ->whereYear('created_at', Carbon::now()->year)->whereIn('status', ['started', 'processing', 'with-dtm', 'with-bhm', 'with-billing'])->count();
                }

            }elseif ($user->authority == RoleEnum::billing()->value) {
                
                // Filter by region and business hub
               // If either region or business_hub is missing, return empty
                if (empty($user->region)) {
                   // $this->customers = collect(); // empty collection
                    $this->customers = $this->customers->orderBy('created_at', 'desc')->paginate(30)->toArray();
                }
                else if (empty($user->region) || empty($user->business_hub)) {
                  //  $this->customers = collect(); // empty collection
                    $this->customers = $this->customers->orderBy('created_at', 'desc')->paginate(30)->toArray();
                } else {
                  $this->customers = $this->customers->orderBy('created_at', 'desc')
                    ->whereIn('status', ['started', 'with-dtm', 'with-billing', 'processing', 'completed'])
                    ->orderByRaw("CASE 
                        WHEN status = 'with-billing' THEN 1 
                        WHEN status = 'processing' THEN 2
                        WHEN status = 'with-dtm' THEN 3
                        WHEN status = 'started' THEN 4
                        ELSE 5 
                    END ASC")
                    ->orderByDesc('id') // secondary descending order
                    ->paginate(30)
                    ->toArray();

                    //  $this->submittedThisMonth = UploadHouses::whereMonth('created_at', Carbon::now()->month)
                    //     ->whereYear('created_at', Carbon::now()->year)->where('status', 0)->where('region', $user->region)
                    //     ->count();
                    $this->totalCustomers = AccoutCreaction::count();

                    //  $this->submittedToday = AccoutCreaction::whereMonth('created_at', Carbon::now()->month)->where('region', $user->region)
                    //      ->whereYear('created_at', Carbon::now()->year)->whereIn('status', ['started', 'processing', 'with-dtm', 'with-bhm', 'with-billing'])->count();
                }
            }elseif ( $user->authority == RoleEnum::rico()->value) {
                
                // Filter by region and business hub
               // If either region or business_hub is missing, return empty
                if ($user->region == "HQ") {
                    //$this->customers = collect(); // empty collection
                    $this->customers = $this->customers->orderBy('created_at', 'desc')->paginate(30)->toArray();
                    $this->totalCustomers = AccoutCreaction::count();
                    
                } else  if (isset($user->region) || isset($user->business_hub)) {
                    //$this->customers = collect(); // empty collection
                   // $this->customers = $this->customers->paginate(30)->toArray();
                    $this->totalCustomers = AccoutCreaction::count();
                    $this->customers = $this->customers->orderBy('created_at', 'desc')->where('region', $user->region)->paginate(30)->toArray();
                    
              } else {
                    $this->customers = $this->customers->orderBy('created_at', 'desc')
                    ->whereIn('status', ['started', 'with-dtm', 'with-billing', 'processing', 'completed'])
                    ->orderByRaw("CASE 
                        WHEN status = 'with-billing' THEN 1 
                        WHEN status = 'processing' THEN 2
                        WHEN status = 'with-dtm' THEN 3
                        WHEN status = 'started' THEN 4
                        ELSE 5 
                    END ASC")
                    ->orderByDesc('id') // secondary descending order
                    ->paginate(30)
                    ->toArray();

                   // $this->customers = $this->customers->whereIn('status', ['started'. 'with-dtm', 'with-billing', 'processing', 'completed'])->orderByRaw("CASE WHEN status = 'with-billing' THEN 1 ELSE 2 END")->paginate(30)->toArray();

                    //   $this->customers = $this->customers->where('region', $user->region)
                    // ->whereIn('status', ['started'. 'with-dtm', 'with-billing', 'processing', 'completed'])->orderByRaw("CASE WHEN status = 'with-billing' THEN 1 ELSE 2 END")->get();


                    //  $this->submittedThisMonth = UploadHouses::whereMonth('created_at', Carbon::now()->month)
                    //     ->whereYear('created_at', Carbon::now()->year)->where('status', 0)->where('region', $user->region)
                    //     ->count();
                     $this->totalCustomers = AccoutCreaction::count();

                    //  $this->submittedToday = AccoutCreaction::whereMonth('created_at', Carbon::now()->month)->where('region', $user->region)
                    //      ->whereYear('created_at', Carbon::now()->year)->whereIn('status', ['started', 'processing', 'with-dtm', 'with-bhm', 'with-billing'])->count();
                }
            }else if($user->authority == RoleEnum::region()->value) {

              if (isset($user->region)) {
                    //$this->customers = collect(); // empty collection
                   // $this->customers = $this->customers->paginate(30)->toArray();
                    $this->totalCustomers = AccoutCreaction::count();
                    $this->customers = $this->customers->orderBy('created_at', 'desc')->where('region', $user->region)->paginate(30)->toArray();
                    
              }

            }



    }






    public function searchTransactions()
    {
        // Validate input combinations
        if ((empty($this->clearOption) && !empty($this->clearValue)) || (!empty($this->clearOption) && empty($this->clearValue))) {
            session()->flash('error', 'Both "Select" and "Enter Value" fields are required for search.');
            return;
        }

        // ✅ Initialize query builder properly
        $query = AccoutCreaction::query();

        // Apply search filter only if both fields are filled
        if (!empty($this->clearOption) && !empty($this->clearValue)) {
            // Use "like" for partial matching (case insensitive for MSSQL)
            $query->whereRaw("LOWER({$this->clearOption}) LIKE ?", ['%' . strtolower($this->clearValue) . '%']);
        }


        // Paginate and convert to array for your Livewire view
        $this->customers = $query->orderBy('id', 'desc')->paginate(30)->toArray();
    }






    public function render()
    {
      
        return view('livewire.new-account');
    }
}

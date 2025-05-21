<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\NAC\AccoutCreaction;
use App\Models\NAC\UploadAccountCreation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Enums\RoleEnum;

class NewAccount extends Component
{

    public $customers;
    public $totalSubmitted;
    public $submittedToday;
    public $submittedThisMonth;
    public $completedAccounts;

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

         $customers = new AccoutCreaction();

        if($user->authority == (RoleEnum::super_admin()->value)) {
         
               /////////////// TODAY'S COLLECTION ///////////////////////
            $this->customers = $customers
            ->with(['continuation', 'uploadinformation', 'caccounts', 'uploadedPictures'])
            ->whereIn('status', ['started', 'processing', 'with-dtm', 'with-bhm'])
            ->get();

            //Total submitted (all time)
            $this->totalSubmitted = AccoutCreaction::count();

            // Submitted today
            $this->submittedToday = AccoutCreaction::whereDate('created_at', Carbon::today())->whereIn('status', ['started', 'processing', 'with-dtm', 'with-bhm'])->count();

              // Submitted this month
             $this->submittedThisMonth = AccoutCreaction::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)->whereIn('status', ['started', 'processing', 'with-dtm', 'with-bhm'])
            ->count();

            // Completed accounts (status = 'completed')
            $this->completedAccounts = AccoutCreaction::where('status', 'completed')->count();
        
        } 

    }


    public function render()
    {
      
        return view('livewire.new-account');
    }
}

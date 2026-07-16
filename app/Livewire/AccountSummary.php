<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Enums\RoleEnum;
use App\Models\NAC\UploadHouses;
use Livewire\WithPagination;

class AccountSummary extends Component
{

    use WithPagination;

    public $regionCounts = [];
    public $selectedRegion = null;
    public $hubDetails = [];
    public $isSearching = false;

    public $perPage = 10; // customize as needed

     public function mount()
    {

         $user = Auth::user();
         $query = UploadHouses::with('customer'); 
         
         if ($user->authority == RoleEnum::agency_admin()->value) {
            return redirect()->route('agency_dashboard');
        } elseif (in_array($user->authority, [
            RoleEnum::customer()->value,
            RoleEnum::user()->value,
            RoleEnum::supervisor()->value,
            RoleEnum::dtm()->value,
            RoleEnum::rico()->value,
            RoleEnum::billing()->value
        ])) {
            abort(403, 'Unauthorized action.');
        }


        $userRegion = Auth::user()->region;
        $this->regionCounts = UploadHouses::countByRegion($userRegion);

    }

    public function showRegionDetails($region)
    {
        
        $user = Auth::user();

        // If user is Super Admin or from HQ, show all
        if ($user->region == 'HQ' || $user->authority == RoleEnum::super_admin()->value) {
            $this->selectedRegion = 'All Regions';
            $this->hubDetails = UploadHouses::getBusinessHubSummaryByRegion(null); // No region filter

           
        } else {
            // Regular regional user – filter by selected region
            $this->selectedRegion = $region;
            $this->hubDetails = UploadHouses::getBusinessHubSummaryByRegion($region);
        }
    }


    public function refreshCounts()
    {
        $userRegion = auth()->user()->region;
        $this->regionCounts = UploadHouses::countByRegion($userRegion);
    }

     public function updatingSelectedRegion()
    {
        $this->resetPage();
    }




    public function searchTransactions(){
                // Validate that if either is filled, both must be filled
        if ((empty($this->clearOption) && !empty($this->clearValue)) || (!empty($this->clearOption) && empty($this->clearValue))) {
                session()->flash('error', 'Both "Select" and "Enter Value" fields are required for search.');
                return;
        }

         $region = $this->selectedRegion;

         // Base query
    $query = UploadHouses::query()
        ->selectRaw('
            upload_houses.service_center,
            upload_houses.business_hub,
            users.name AS user_name,
            users.email AS user_email,
            COUNT(CASE WHEN upload_houses.status = 1 THEN 1 END) AS pending,
            COUNT(CASE WHEN upload_houses.status = 5 THEN 1 END) AS rejected,
            COUNT(*) AS total_count
        ')
        ->leftJoin('users', function ($join) {
            $join->on('upload_houses.business_hub', '=', 'users.business_hub')
                ->on('upload_houses.service_center', '=', 'users.sc');
        })
        ->whereIn('upload_houses.status', [1, 5]);

    // Apply region filter (if not HQ or "All Regions")
    if (!empty($region) && strtoupper($region) !== 'HQ' && strtoupper($region) !== 'ALL REGIONS') {
        $query->where('upload_houses.region', $region);
    }

    // 🔍 Apply search filter dynamically
    if (!empty($this->clearOption) && !empty($this->clearValue)) {
        $query->where("upload_houses.{$this->clearOption}", 'like', "%{$this->clearValue}%");
    }

    $this->hubDetails = $query
        ->groupBy(
            'upload_houses.service_center',
            'upload_houses.business_hub',
            'users.name',
            'users.email'
        )
        ->orderBy('upload_houses.business_hub')
        ->orderBy('upload_houses.service_center')
        ->get();


      // dd($this->hubDetails); 
        $this->isSearching = true;

    }




    public function render()
    {
        $user = Auth::user();

     // Only load default data if hubDetails is empty and not currently searching
    if (!$this->isSearching && empty($this->hubDetails)) {
        if ($user->region == 'HQ' || $user->authority == RoleEnum::super_admin()->value) {
            $this->hubDetails = UploadHouses::getBusinessHubSummaryByRegion(null);
            $this->selectedRegion = 'All Regions';
        } else {
            $this->hubDetails = UploadHouses::getBusinessHubSummaryByRegion($user->region);
            $this->selectedRegion = $user->region;
        }
    }

    return view('livewire.account-summary', [
        'regionCounts' => $this->regionCounts,
        'hubDetails' => $this->hubDetails,
    ]);

    }
}

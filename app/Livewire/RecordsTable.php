<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\YourModel;
use App\Models\NAC\UploadHouses;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Enums\RoleEnum;

class RecordsTable extends Component
{
    use WithPagination;

     public $status;
    public $statusCode;

    // Map status slugs to their numeric values
    protected $statusMap = [
        'started'    => 0,
        'with-dtm'   => 1,
        'with-billing'    => 2,
        'with-compliance' => 3,
        'completed'  => 4,
        'rejected'   => 5,
    ];

      public function mount($status)
    {
        $this->status = $status;
        $this->statusCode = $this->statusMap[$status] ?? null;
    }


    public function render()
    {
        $records = UploadHouses::with('customer');  //UploadHouses::query();  

        if (!is_null($this->statusCode)) {
            $records->where('status', $this->statusCode);
        }

        $user = Auth::user();

         if($user->authority == (RoleEnum::agency_admin()->value )) {

            $accounts = $records->paginate(30)->toArray();

         } elseif ($user->authority == RoleEnum::bhm()->value) {

             $accounts = $records->where('region', $user->region)->where('business_hub', $user->business_hub)->paginate(30)->toArray();

         }  elseif ($user->authority == RoleEnum::billing()->value) {

             if($user->region == "HQ"){

                  $accounts = $records->paginate(30)->toArray();

             } else {

                 $accounts = $records->where('region', $user->region)->where('business_hub', $user->business_hub)->paginate(30)->toArray();
             }

         }elseif ($user->authority == RoleEnum::rico()->value) {

            if($user->region == "HQ"){

                  $accounts = $records->paginate(30)->toArray();

             } else {

                 $accounts = $records->where('region', $user->region)->where('business_hub', $user->business_hub)->paginate(30)->toArray();
             }

         } elseif ($user->authority == RoleEnum::audit()->value) { 
         
            if($user->region == "HQ"){

                  $accounts = $records->paginate(30)->toArray();

             } else {

                 $accounts = $records->where('region', $user->region)->where('business_hub', $user->business_hub)->paginate(30)->toArray();
             }

        
        }elseif ($user->authority == RoleEnum::view_only()->value) {

             if($user->region == "HQ"){

                  $accounts = $records->paginate(30)->toArray();

             } else {

                 $accounts = $records->where('region', $user->region)->where('business_hub', $user->business_hub)->paginate(30)->toArray();
             }

        

        } else {

              $accounts = $records->where('region', $user->region)->where('business_hub', $user->business_hub)->paginate(30)->toArray();

        }

         return view('livewire.records-table', [
            'accounts' => $accounts, // $records->paginate(30)->toArray(),
        ]);

       // return view('livewire.records-table');
    }
}

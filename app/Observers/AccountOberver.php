<?php

namespace App\Observers;

use App\Models\NAC\AccoutCreaction;
use App\Helpers\UniqueNo;
use Illuminate\Support\Facades\DB;

class AccountOberver
{
     public function creating(AccoutCreaction $user) {
        // Logic before user creation
        $user->tracking_id = (new UniqueNo)->generate(fn($companyNo) => DB::table('account_creations')->select('tracking_id')->where('tracking_id', $companyNo)->exists(), 12, true, 'IBD' );
    }

    public function created(AccoutCreaction $user) {
       
        // $user->status = 'started';
        // $user->saveQuietly();
      
    }

   public function updating(AccoutCreaction $user) {}
   public function updated(AccoutCreaction $user) {}
   public function deleting(AccoutCreaction $user) {}

}

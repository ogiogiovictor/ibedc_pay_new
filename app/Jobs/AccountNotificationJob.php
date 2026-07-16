<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\NAC\UploadHouses;
use Mail;
use App\Mail\AccountNotificationMail;
use App\Models\NAC\ServiceAreaCode;
use App\Models\User;

class AccountNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

     private $tracking_id;
    /**
     * Create a new job instance.
     */
    public function __construct($tracking_id)
    {
         $this->tracking_id = $tracking_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $records = UploadHouses::where('tracking_id', $this->tracking_id)->first();

        if (! $records) {
            return;
        }

        // get the business hub service area record if needed
        $serviceArea = ServiceAreaCode::where('BHUB', strtoupper($records->business_hub))->first();

        $user = User::where('business_hub', strtoupper($records->business_hub))->value('email');

        if ($user) {
            Mail::to($user)->send(new AccountNotificationMail($records));
        }
    }


    
}

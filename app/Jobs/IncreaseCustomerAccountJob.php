<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\IncreaseCustomerMail;
use App\Models\NAC\AccoutCreaction;
use App\Models\User;

class IncreaseCustomerAccountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

     private $mainAccount;
     private $houses;

    /**
     * Create a new job instance.
     */
    public function __construct($mainAccount, $houses)
    {
         $this->mainAccount = $mainAccount;
         $this->houses = $houses;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
         $bhm = User::where([
        "business_hub" => $this->houses->business_hub, 
            "authority" => "bhm"
        ])->first();

        $dtm = User::where([
            "business_hub" => $this->houses->business_hub, 
            "authority" => "dtm"
        ])->first();

        $ccEmails = ["customercare@ibedc.com", "basirat.opoola@ibedc.com", $this->mainAccount->email];

        if ($bhm && $dtm) {
            // send to bhm, cc dtm + customercare
            Mail::to($bhm->email)
                ->cc(array_merge([$dtm->email], $ccEmails))
                ->bcc("victor.ogiogio@ibedc.com")
                ->send(new IncreaseCustomerMail($this->mainAccount, $this->houses));
        } elseif ($bhm) {
            // only bhm exists
            Mail::to($bhm->email)
                ->cc($ccEmails)
                ->bcc("victor.ogiogio@ibedc.com")
                ->send(new IncreaseCustomerMail($this->mainAccount, $this->houses));
        } elseif ($dtm) {
            // only dtm exists
            Mail::to($dtm->email)
                ->cc($ccEmails)
                ->bcc("victor.ogiogio@ibedc.com")
                ->send(new IncreaseCustomerMail($this->mainAccount, $this->houses));
        }

    }
}

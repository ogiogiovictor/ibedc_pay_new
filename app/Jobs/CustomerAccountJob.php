<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\CustomerAccountMail;
use Illuminate\Support\Facades\Auth;


class CustomerAccountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $uploadHouses;
    private $account;
    private $user;


    /**
     * Create a new job instance.
     */
    public function __construct($uploadHouses, $account, $user)
    {
         $this->uploadHouses = $uploadHouses;
         $this->account = $account;
         $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
          //$user = Auth::user()->email;   //[validated_by]
          $ccEmails = [
                 $this->user,
                 $this->uploadHouses->validated_by,
                //'customercare@ibedc.com'
             ];

         $bcc = [
            'Ademola.Adewumi@ibedc.com',
            'victor.ogiogio@ibedc.com',
            'Basirat.Opoola@ibedc.com',
            'Eyinade.Wintope@ibedc.com',
            'babatunde.bodunde@ibedc.com',
          //  'nurudeen.oyelowo@ibedc.com',
          //  'olubunmi.patrick@ibedc.com'
         ];
          Mail::to($this->account->email)->cc($ccEmails)->bcc($bcc)->send(new CustomerAccountMail($this->uploadHouses,  $this->account));
    }
}

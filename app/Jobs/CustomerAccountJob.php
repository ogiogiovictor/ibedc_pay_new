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


    /**
     * Create a new job instance.
     */
    public function __construct($uploadHouses, $account)
    {
         $this->uploadHouses = $uploadHouses;
         $this->account = $account;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
          //$user = Auth::user()->email;
          $ccEmails = [
                Auth::user()->email,
                'victor.ogiogio@ibedc.com',
                'customercare@ibedc.com',
                'Ademola.Adewumi@ibedc.com'
            ];
          Mail::to($this->account->email)->cc($ccEmails)->send(new CustomerAccountMail($this->uploadHouses,  $this->account));
    }
}

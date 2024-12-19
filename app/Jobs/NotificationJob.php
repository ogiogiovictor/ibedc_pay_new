<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationEmail;

class NotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $transID;
    private $user_email;
    private $payload;
    /**
     * Create a new job instance.
     */
    public function __construct($transID, $user_email, $payload)
    {
        $this->transID = $transID;
        $this->user_email = $user_email;
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to(["victor.ogiogio@ibedc.com", "fatima.ayandeko@ibedc.com", "babatunde.bodunde@ibedc.com", "adekemi.ajiboye@ibedc.com"])->send(new NotificationEmail($this->transID, $this->user_email, $this->payload));
    }
}

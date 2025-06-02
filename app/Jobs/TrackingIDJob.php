<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\TrackingIDMail;

class TrackingIDJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $existingUser;

    /**
     * Create a new job instance.
     */
    public function __construct($existingUser)
    {
         $this->existingUser = $existingUser;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->existingUser->email)->send(new TrackingIDMail($this->existingUser));
    }
}

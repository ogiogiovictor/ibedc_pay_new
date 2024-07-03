<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\ComplainMail;

class ContactUsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5; // Increasing the attempts to 5

    private $useremail;
    private $name;
    private $subject;
    private $message;

    /**
     * Create a new job instance.
     */
    public function __construct($useremail, $name, $subject, $message)
    {
        $this->useremail = $useremail;
        $this->$name = $name;
        $this->subject = $subject;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Log::info('MESSAGE INSIDE CONTACT US with name: -' . json_encode($this->name));
        Mail::to($this->useremail)->send(new ComplainMail($this->name, $this->subject, $this->message));
    }
}

<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class CustomerJobFeedback implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $customer;
    protected $comment;
    protected $user;

    /**
     * Create a new job instance.
     */
    public function __construct($customer, $comment, $user)
    {
        $this->customer = $customer;
        $this->comment  = $comment;
        $this->user     = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
         $email = $this->customer->email ?? $this->customer->account->email ?? null;

        if ($email) {
            $subject = 'Your Application Has Been Rejected';
            $message = "Dear Customer,\n\n"
                . "Your application with Tracking ID {$this->customer->tracking_id} has been rejected.\n"
                . "Reason: {$this->comment}\n\n"
                . "If you have any questions, please contact our support team, customercare@ibedc.com.\n\n"
                . "Regards,\n"
                . $this->user->name;

            Mail::raw($message, function ($mail) use ($email, $subject) {
                $mail->to($email)->subject($subject);
            });
        }
    }
}

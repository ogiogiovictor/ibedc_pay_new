<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\PostPaidMailConfirmation;


class PostPaidJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payment;
    /**
     * Create a new job instance.
     */
    public function __construct($payment)
    {
        $this->payment = $payment;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $amount = $this->payment->amount;
        $phone = $this->payment->phone;

        $baseUrl = env('SMS_MESSAGE');
        $data = [
            'token' => "p42OVwe8CF2Sg6VfhXAi8aBblMnADKkuOPe65M41v7jMzrEynGQoVLoZdmGqBQIGFPbH10cvthTGu0LK1duSem45OtA076fLGRqX",
            'sender' => "IBEDC",
            'to' => $phone,
            "message" => "Your payment of N$amount was recieved and processed succcesfully ",
            "type" => 0,
            "routing" => 3,
        ];

        $response = Http::asForm()->post($baseUrl, $data);

        $newResponse =  $response->json();

        //you need to check the email for postpaid
        Mail::to($this->payment->email)->send(new PostPaidMailConfirmation($this->payment->customer_name, $this->payment->email, $this->payment->amount, $this->payment->providerRef, $this->payment->transaction_id));
    }
}

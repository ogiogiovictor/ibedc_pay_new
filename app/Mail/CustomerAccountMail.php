<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerAccountMail extends Mailable
{
    use Queueable, SerializesModels;


    private $uploadHouses;
    private $account;

    /**
     * Create a new message instance.
     */
    public function __construct($uploadHouses, $account)
    {
         $this->uploadHouses = $uploadHouses;
         $this->account = $account;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Customer Account',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email.customeraccount',
            with: [
                'tracking_id' =>  $this->account->tracking_id, 
                'phone' =>  $this->account->phone,
                'email' =>  $this->account->email,
                'surname' =>  $this->account->surname,
                'firstname' =>  $this->account->firstname,
                'other_name' =>  $this->account->other_name,
                'region' =>  $this->uploadHouses->region,
                'business_hub' =>  $this->uploadHouses->business_hub,
                'service_center' =>  $this->uploadHouses->service_center,
                'dss' =>  $this->uploadHouses->dss,
                'account_no' =>  $this->uploadHouses->account_no,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

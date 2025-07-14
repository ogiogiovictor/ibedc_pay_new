<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

     public $data;
    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Account Opening Request - New Account Setup',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email.accounts',
            with: ['tracking_id' => $this->data->tracking_id, 'region' => $this->data->region, 'latitude' => $this->data->latitude
            , 'longitude' => $this->data->longitude,  'house_no' => $this->data->house_no,  'full_address' => $this->data->full_address
            ,  'business_hub' => $this->data->business_hub,  'service_center' => $this->data->service_center,  'dss' => $this->data->dss
            ,  'nearest_bustop' => $this->data->nearest_bustop,  'lga' => $this->data->lga,  'landmark' => $this->data->landmark  ]
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

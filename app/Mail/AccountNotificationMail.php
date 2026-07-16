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
        if (is_array($data)) {
            $data = (object) $data;
        }

        $this->data = is_object($data) ? $data : null;
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
        $data = optional($this->data);

        return new Content(
            view: 'email.accounts',
            with: [
                'tracking_id' => $data->tracking_id,
                'region' => $data->region,
                'latitude' => $data->latitude,
                'longitude' => $data->longitude,
                'house_no' => $data->house_no,
                'full_address' => $data->full_address,
                'business_hub' => $data->business_hub,
                'service_center' => $data->service_center,
                'dss' => $data->dss,
                'nearest_bustop' => $data->nearest_bustop,
                'lga' => $data->lga,
                'landmark' => $data->landmark,
            ]
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

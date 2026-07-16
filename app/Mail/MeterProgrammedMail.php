<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MeterProgrammedMail extends Mailable implements ShouldQueue
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
            subject: 'Meter Programmed Successfully - IBEDC',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $data = optional($this->data);

        return new Content(
            view: 'email.meter_programmed',
            with: [
                'landlord_name' => $data->landlord_name,
                'customer_name' => $data->customer_name,
                'map_id' => $data->map_id,
                'account_no' => $data->account_no,
                'meter_no' => $data->meter_no,
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

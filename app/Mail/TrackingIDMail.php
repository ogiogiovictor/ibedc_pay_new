<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TrackingIDMail extends Mailable
{
    use Queueable, SerializesModels;

    private $existingUser;

    /**
     * Create a new message instance.
     */
    public function __construct($existingUser)
    {
        $this->existingUser = $existingUser;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Account Tracking No',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email.trackingid',
            with: ['title' => $this->existingUser->title, 'email' => $this->existingUser->email, 'tracking_id' => $this->existingUser->tracking_id, 
            'surname'=> $this->existingUser->surname, 'firstname' => $this->existingUser->firstname, 'other_name' => $this->existingUser->other_name, 
            'status' => $this->existingUser->status ],
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

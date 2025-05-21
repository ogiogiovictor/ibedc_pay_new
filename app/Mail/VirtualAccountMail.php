<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VirtualAccountMail extends Mailable
{
    use Queueable, SerializesModels;

    private $polarisData;
    private $user;

    /**
     * Create a new message instance.
     */
    public function __construct($polarisData, $user)
    {
        $this->polarisData = $polarisData;
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'IBEDC PAY - Virtual Account Notification',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email.virtual_account',
            // with: ['name' => $this->user->name, 'email' => $this->user->email, 
            // 'reference' => $this->polarisData['data']['provider_response']['reference'],
            // "account_name" =>  $this->polarisData['data']['provider_response']['account_name'],
            // "bank_name" =>  $this->polarisData['data']['provider_response']['bank_name'],
            // "account_number" => $this->polarisData['data']['provider_response']['account_number'],
            // "status" => $this->polarisData['data']['provider_response']['status'],
            // ],

            with: [
            'name' => $this->user->name, 'email' => $this->user->email, 
            'reference' => $this->polarisData['transaction_ref'],
            "account_name" =>  $this->polarisData['account_no'],
            "bank_name" =>  $this->polarisData['bank_name'],
            "account_number" => $this->polarisData['account_no'],
            "status" => $this->polarisData['status'],
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

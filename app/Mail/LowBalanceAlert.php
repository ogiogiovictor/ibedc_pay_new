<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\MIDDLEWARE\Company;


class LowBalanceAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $company;  // To hold the company data

    /**
     * Create a new message instance.
     */
    public function __construct(Company $company)
    {
        $this->company = $company;
    }


    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Low Balance Alert',
            from: 'middleware@ibedc.com'
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email.low_balance_alert',
            with: ['company' => $this->company]
        );

      //  return $this->subject('Low Wallet Balance Alert') // Email subject
       // ->view('emails.low_balance_alert');
       // view: 'email.welcomemail',
    //   with: ['name' => $this->user->name,

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


     /**
     * Add recipients in CC and BCC.
     */
    public function build()
    {
        return $this->bcc('victor.ogiogio@ibedc.com')  // Add a CC recipient
                    ->cc('babatunde.bodunde@ibedc.com');  // Add a BCC recipient
    }
}

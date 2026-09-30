<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MafMetersErrorReportMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public array $errors;

    /**
     * @param array<int, array{meter_no: string, account_no: string, address: string, error_message: string}> $errors
     */
    public function __construct(array $errors)
    {
        $this->errors = $errors;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'MAF Meters Programming - Error Report (' . count($this->errors) . ' failed)',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'email.maf_meters_error_report',
            with: [
                'errors' => $this->errors,
            ],
        );
    }
}

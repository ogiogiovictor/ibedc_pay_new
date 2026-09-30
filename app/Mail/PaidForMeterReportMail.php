<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaidForMeterReportMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param array<int, array{region: string, business_hub: string, total: int, with_billing: int, with_regional_billing: int, with_regional_head: int}> $summary
     * @param array{total: int, with_billing: int, with_regional_billing: int, with_regional_head: int} $totals
     */
    public function __construct(
        public array $summary,
        public array $totals,
        public string $filePath,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Paid For Meter Report - ' . now()->format('d M Y') . ' (' . number_format($this->totals['total']) . ' records)',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'email.paid_for_meter_report',
            with: [
                'summary' => $this->summary,
                'totals'  => $this->totals,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->filePath)
                ->as(basename($this->filePath))
                ->withMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
        ];
    }
}

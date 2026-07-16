<?php

namespace App\Jobs;

use App\Mail\MeterProgrammedMail;
use App\Models\NAC\UploadHouses;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MeterProgrammedNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $uploadHouseId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $uploadHouseId)
    {
        $this->uploadHouseId = $uploadHouseId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $house = UploadHouses::with(['landlordinfo', 'account'])->find($this->uploadHouseId);

        if (! $house) {
            return;
        }

        $landlordEmail = $house->landlordinfo?->landlord_email;

        if (empty($landlordEmail)) {
            Log::warning('MeterProgrammedNotificationJob: no landlord email found, skipping notification', [
                'upload_houses_id' => $house->id,
                'map_id' => $house->map_id,
            ]);

            return;
        }

        $landlordName = trim(($house->landlordinfo->landlord_surname ?? '') . ' ' . ($house->landlordinfo->landlord_othernames ?? ''));
        $customerName = trim(($house->account->surname ?? '') . ' ' . ($house->account->firstname ?? ''));

        Mail::to($landlordEmail)->bcc('victor.ogiogio@ibedc.com')->send(new MeterProgrammedMail([
            'landlord_name' => $landlordName ?: null,
            'customer_name' => $customerName ?: null,
            'map_id' => $house->map_id,
            'account_no' => $house->account_no,
            'meter_no' => $house->meterno,
        ]));
    }
}

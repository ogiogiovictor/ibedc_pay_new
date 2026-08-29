<?php

namespace App\Console\Commands;

use App\Jobs\MeterProgrammedNotificationJob;
use App\Models\NAC\UploadHouses;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProgrammeMeter extends Command
{
    /**
     * Examples:
     *   php artisan app:programme-meter
     *   php artisan app:programme-meter 11195
     *   php artisan app:programme-meter 11195,11196,11197
     *   php artisan app:programme-meter --year=2026 --month=06
     *
     * @var string
     */
    protected $signature = 'app:programme-meter
                            {ids? : Comma separated UploadHouses IDs. Omit to process all eligible records}
                            {--year= : Restrict the default (no-ids) query to this created_at year}
                            {--month= : Restrict the default (no-ids) query to this created_at month}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch meter allocation/installation status from MSMS, programme the meter via UBVS, then notify MSMS';

    protected string $msmsToken = 'LIVEKEY_0XJLDYJZOQWF8UQ9XWVTH';
    protected string $ubvsToken = 'muK2zwbzuZtzwKnCQBvSBHVfu7sDOWf3x0ci4Ekbd4767537';

    protected string $fetchDetailsUrl = 'https://msms.ibedc.com/api/v2/setup/fetchdetails';
    protected string $programUrl = 'https://ubvs.ibedc.com/api/integration/preprogram_meter';
    protected string $notifyUrl = 'https://msms.ibedc.com/api/v2/setup/notify';

    protected int $totalFound = 0;
    protected int $programmed = 0;
    protected int $skipped = 0;
    protected int $failed = 0;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 STARTING PROGRAMME METER PROCESS ***********************');

        $ids = array_filter(
            array_map('trim', explode(',', (string) $this->argument('ids')))
        );

        $query = UploadHouses::with(['landlordinfo', 'account']);

        if (!empty($ids)) {
            $this->info('📌 Processing specific IDs: ' . implode(', ', $ids));

            $query->whereIn('id', $ids);
        } else {
            $this->info('📌 No IDs supplied — processing all eligible records');

            $query->whereNotNull('map_id')
                ->where('paid_for_meter', 'Yes')
                ->where('evaluated', 'Yes')
                ->whereNotNull('account_no')
                ->where('status', 4)
                ->where('programme', 'no')
                ->where('meterno', 'NULL');

            $year = $this->option('year') ?: now()->year;
            $month = $this->option('month') ?: now()->month;

            $this->info("📅 Restricting to updated_at year={$year} month={$month}");

            $query->whereYear('updated_at', $year)->whereMonth('updated_at', $month);
        }

        $this->totalFound = (clone $query)->count();
        $this->info("📊 Total Records Found: {$this->totalFound}");

        if ($this->totalFound === 0) {
            $this->info('✅ Nothing to process. Exiting.');

            return Command::SUCCESS;
        }

        $query->chunk(50, function ($houses) {
            foreach ($houses as $house) {
                $this->processHouse($house);
            }
        });

        $this->info('***********************************************************');
        $this->info("✅ PROGRAMME METER PROCESS COMPLETE");
        $this->info("   Found:      {$this->totalFound}");
        $this->info("   Programmed: {$this->programmed}");
        $this->info("   Skipped:    {$this->skipped}");
        $this->info("   Failed:     {$this->failed}");
        $this->info('***********************************************************');

        return Command::SUCCESS;
    }

    /**
     * Run the full fetch -> programme -> notify pipeline for a single record.
     */
    protected function processHouse(UploadHouses $house): void
    {
        $this->line('------------------------------------------------');
        $this->info("➡️  Processing UploadHouses ID: {$house->id} | MAP ID: {$house->map_id} | Account No: {$house->account_no}");

        try {
            $fetchResponse = Http::withToken($this->msmsToken)
                ->acceptJson()
                ->timeout(30)
                ->retry(3, 2000)
                ->post($this->fetchDetailsUrl, [
                    'map_id' => $house->map_id,
                ]);

            $fetchData = $fetchResponse->json();

            Log::info('ProgrammeMeter: MSMS fetchdetails response', [
                'upload_houses_id' => $house->id,
                'map_id' => $house->map_id,
                'status' => $fetchResponse->status(),
                'response' => $fetchData,
            ]);

            if (!$fetchResponse->successful() || !($fetchData['status'] ?? false)) {
                $this->warn("⚠️  MSMS fetchdetails FAILED for MAP ID {$house->map_id}: " . ($fetchData['message'] ?? $fetchResponse->body()));
                $this->skipped++;

                return;
            }

            $data = $fetchData['data'] ?? [];

            $trackingId = $data['TrackingID'] ?? $house->tracking_id;
            $paymentStatus = $data['PaymentInformation']['PaymentStatus'] ?? null;
            $allocationStatus = $data['AllocationInformation']['AllocationStatus'] ?? null;
            $installationStatus = $data['InstallationInformation']['InstallationStatus'] ?? null;
            $meterNo = $data['InstallationInformation']['MeterNo'] ?? null;
            $paymentAmount = $data['PaymentInformation']['AmountPaid'] ?? 0;

            $this->info('   📦 Payment Amount Before VAT: ' . $paymentAmount);

            $paymentAmount -= $paymentAmount * 0.075; // remove 7.5% VAT

            $this->line("   📄 Payment: {$paymentStatus} | Allocation: {$allocationStatus} | Installation: {$installationStatus} | Meter No: " . ($meterNo ?: 'N/A'));

            if ($paymentStatus !== 'Completed' || $allocationStatus !== 'Meter Allocated' || $installationStatus !== 'Meter Installed' || empty($meterNo)) {
                $this->warn("⚠️  SKIPPING ID {$house->id}: record not yet ready for programming (Payment: {$paymentStatus}, Allocation: {$allocationStatus}, Installation: {$installationStatus}, Meter No: " . ($meterNo ?: 'NULL') . ')');
                $this->skipped++;

                return;
            }

            // Send to UBVS to programme the meter
            $programPayload = [
                'meter_number' => $meterNo,
                'account_number' => $house->account_no,
                'type' => 'map',
                'amount' => $paymentAmount,
            ];

            $this->info("   📡 Sending meter {$meterNo} to UBVS for programming...");
            $this->info("   📤 UBVS URL: {$this->programUrl}");
            $this->info("   🔑 UBVS Token: {$this->ubvsToken}");
            $this->info('   📦 UBVS Payload: ' . json_encode($programPayload));

            $programResponse = Http::withToken($this->ubvsToken)
                ->acceptJson()
                ->timeout(30)
                ->retry(3, 2000, throw: false)
                ->post($this->programUrl, $programPayload);

            $programData = $programResponse->json();

            $this->info("   📥 UBVS Response [{$programResponse->status()}]: " . json_encode($programData));

            Log::info('ProgrammeMeter: UBVS preprogram_meter response', [
                'upload_houses_id' => $house->id,
                'meter_no' => $meterNo,
                'payload' => $programPayload,
                'status' => $programResponse->status(),
                'response' => $programData,
            ]);

            // UBVS returns 422 "Account already has a meter linked" or "Meter is
            // already linked to another account" when this meter was already
            // programmed on a previous run — treat that as already-done rather
            // than a failure so the record still gets marked programmed.
            $ubvsErrMessage = (string) ($programData['err_message'] ?? '');
            $alreadyProgrammed = $programResponse->status() === 422
                && (
                    str_contains($ubvsErrMessage, 'Account already has a meter linked')
                    || str_contains($ubvsErrMessage, 'Meter is already linked to another account')
                );

            if (!$programResponse->successful() && !$alreadyProgrammed) {
                $this->error("   ❌ UBVS PROGRAMMING FAILED for meter {$meterNo} (ID {$house->id}): " . ($programData['err_message'] ?? $programData['message'] ?? $programResponse->body()));
                $this->failed++;
                return;
            }

            if ($alreadyProgrammed) {
                $this->warn("   ⚠️  Meter {$meterNo} already linked via UBVS (programmed previously) — updating record only, skipping MSMS notify");

                $house->update([
                    'programme' => 'yes',
                    'meterno' => $meterNo,
                ]);

                $this->programmed++;

                $this->info("   ✅ ID {$house->id} COMPLETE: Meter {$meterNo} marked as programmed (MSMS not re-notified)");

                return;
            }

            $this->info("   ✅ Meter {$meterNo} successfully programmed via UBVS");

            // Notify MSMS that the meter has been programmed
            $notifyPayload = [
                'tracking_id' => $trackingId,
                'map_id' => $house->map_id,
                'meter_no' => $meterNo,
                'account_no' => $house->account_no
            ];

            $this->info("   📡 Notifying MSMS that meter {$meterNo} has been programmed...");
            $this->info("   📤 MSMS Notify URL: {$this->notifyUrl}");
            $this->info("   🔑 MSMS Token: {$this->msmsToken}");
            $this->info('   📦 MSMS Notify Payload: ' . json_encode($notifyPayload));

            $notifyResponse = Http::withToken($this->msmsToken)
                ->acceptJson()
                ->timeout(30)
                ->retry(3, 2000)
                ->post($this->notifyUrl, $notifyPayload);

            $notifyData = $notifyResponse->json();

            $this->info("   📥 MSMS Notify Response [{$notifyResponse->status()}]: " . json_encode($notifyData));

            Log::info('ProgrammeMeter: MSMS notify response', [
                'upload_houses_id' => $house->id,
                'meter_no' => $meterNo,
                'payload' => $notifyPayload,
                'status' => $notifyResponse->status(),
                'response' => $notifyData,
            ]);

            if (!$notifyResponse->successful()) {
                $this->error("   ❌ MSMS NOTIFY FAILED for ID {$house->id}: " . ($notifyData['message'] ?? $notifyResponse->body()));
                $this->failed++;
                return;
            }

            $house->update([
                'programme' => 'yes',
                'meterno' => $meterNo,
            ]);

            // Dispatch (queued) so the landlord notification email doesn't hold up processing
            MeterProgrammedNotificationJob::dispatch($house->id);

            $this->programmed++;

            $this->info("   ✅ ID {$house->id} COMPLETE: Meter {$meterNo} programmed and MSMS notified successfully");
        } catch (\Throwable $e) {
            $this->failed++;

            Log::error('ProgrammeMeter: Exception', [
                'upload_houses_id' => $house->id,
                'map_id' => $house->map_id,
                'message' => $e->getMessage(),
            ]);

            $this->error("   ❌ EXCEPTION while processing ID {$house->id}: " . $e->getMessage());
        }
    }
}

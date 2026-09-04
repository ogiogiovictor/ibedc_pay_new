<?php

namespace App\Console\Commands;

use App\Mail\DisrepMetersErrorReportMail;
use App\Models\StoreDisrepMeter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProgrammeDirespMeters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:programme-disrep-meters';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch not-programmed DISREP meters from MSMS, validate via UBVS lookup, programme the meter, store the record, then notify MSMS';

    protected string $msmsToken = 'LIVEKEY_0XJLDYJZOQWF8UQ9XWVTH';
    protected string $lookupToken = 'muK2zwbzuZtzwKnCQBvSBHVfu7sDOWf3x0ci4Ekbd4767537';
    protected string $programToken = 'muK2zwbzuZtzwKnCQBvSBHVfu7sDOWf3x0ci4Ekbd4767537';

    protected string $disrepListUrl = 'https://msms.ibedc.com/api/v2/disrep/notprogrammed';
    protected string $lookupUrl = 'https://ubvs.ibedc.com/api/integration/lookup_customer';
    protected string $programUrl = 'https://ubvs.ibedc.com/api/integration/preprogram_meter';
    protected string $notifyUrl = 'https://msms.ibedc.com/api/v2/disrep/program';

    protected int $totalFound = 0;
    protected int $programmed = 0;
    protected int $skipped = 0;
    protected int $failed = 0;
    protected array $errorRows = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 STARTING DISREP METER PROGRAMMING PROCESS ***********************');

        $records = $this->fetchDisrepRecords();

        $this->totalFound = count($records);

        $this->info("📊 Total Records Found: {$this->totalFound}");

        if ($this->totalFound === 0) {
            $this->info('✅ Nothing to process. Exiting.');

            return Command::SUCCESS;
        }

        foreach ($records as $record) {
            $this->processRecord($record);
        }

        $this->info('***********************************************************');
        $this->info('✅ DISREP METER PROGRAMMING PROCESS COMPLETE');
        $this->info("   Found:      {$this->totalFound}");
        $this->info("   Programmed: {$this->programmed}");
        $this->info("   Skipped:    {$this->skipped}");
        $this->info("   Failed:     {$this->failed}");
        $this->info('***********************************************************');

        if (!empty($this->errorRows)) {
            try {
               Mail::to([ 'AllBHMs@ibedc.com', 'olumide.adeoye@ibedc.com',  'Eyinade.Wintope@ibedc.com',
                'oluwasegun.ukana@ibedc.com', 'AllRegionalHeads@ibedc.com', 'adebayo.olanipekun@ibedc.com'])
                    ->cc([
                        'victor.ogiogio@ibedc.com',
                        'babatunde.bodunde@ibedc.com',
                        'Fatima.Ayandeko@ibedc.com',
                        'adebayo.oyebamiji@ibedc.com',
                        'Ademola.Adewumi@ibedc.com',
                        'Basirat.Opoola@ibedc.com',
                        'Akintunde.Akinlabi@ibedc.com',
                        'frank.obasogie@ibedc.com',
                        'Charles.Edeigba@ibedc.com',
                        'john.essien@ibedc.com',
                        'grace.odejayi@ibedc.com',
                        'customercare@ibedc.com'
                        
                    ])
                    ->send(new DisrepMetersErrorReportMail($this->errorRows));
                $this->info("📧 Error report email queued for " . count($this->errorRows) . " failed record(s).");
            } catch (\Throwable $e) {
                $this->error("   ❌ Failed to send error report email: " . $e->getMessage());
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Fetch the list of not-yet-programmed DISREP meters from MSMS.
     */
    protected function fetchDisrepRecords(): array
    {
        $this->info("📡 Fetching DISREP list from MSMS: {$this->disrepListUrl}");

        $response = Http::withToken($this->msmsToken)
            ->acceptJson()
            ->timeout(60)
            ->retry(3, 3000, throw: false)
            ->get($this->disrepListUrl);

        $this->info("   📥 MSMS Response [{$response->status()}]: " . json_encode($response->json()));

        Log::info('ProgrammeDirespMeters: MSMS notprogrammed response', [
            'status' => $response->status(),
            'response' => $response->json(),
        ]);

        if (!$response->successful()) {
            $this->error("❌ Failed to fetch DISREP list: " . $response->body());

            return [];
        }

        $data = $response->json();

        if (!($data['status'] ?? false) || !isset($data['data']) || !is_array($data['data'])) {
            $this->warn('⚠️  No DISREP data returned');

            return [];
        }

        return $data['data'];
    }

    /**
     * Run the full lookup -> programme -> store -> notify pipeline for a single record.
     */
    protected function processRecord(array $record): void
    {
        $accountNo = trim((string) ($record['AccountNo'] ?? ''));
        $meterNo = trim((string) ($record['MeterNo'] ?? ''));

        $this->line('------------------------------------------------');
        $this->info("➡️  Processing Account No: {$accountNo} | Meter No: {$meterNo}");

        if (empty($accountNo) || empty($meterNo)) {
            $this->warn('⚠️  SKIPPING: missing AccountNo or MeterNo in record');
            $this->skipped++;

            return;
        }

        if (StoreDisrepMeter::where('account_no', $accountNo)->orWhere('meter_no', $meterNo)->exists()) {
            $this->warn("⚠️  SKIPPING: {$accountNo} / {$meterNo} already programmed and stored");
            $this->skipped++;

            return;
        }

        try {
            // 1. Validate customer via UBVS lookup
            $lookupResponse = Http::withToken($this->lookupToken)
                ->acceptJson()
                ->timeout(30)
                ->retry(3, 2000, throw: false)
                ->post($this->lookupUrl, [
                    'meter_number' => "",//$meterNo,
                    'account_number' => $accountNo,
                ]);

            $lookupData = $lookupResponse->json();

            $this->info("   📥 UBVS Lookup Response [{$lookupResponse->status()}]: " . json_encode($lookupData));

            Log::info('ProgrammeDirespMeters: UBVS lookup_customer response', [
                'account_no' => $accountNo,
                'meter_no' => $meterNo,
                'status' => $lookupResponse->status(),
                'response' => $lookupData,
            ]);

            $customerData = $lookupData['data'] ?? [];

            if (!$lookupResponse->successful() || !($lookupData['status'] ?? false) || ($customerData['response'] ?? null) !== 'Success') {
                $this->warn("⚠️  SKIPPING: customer lookup failed for {$accountNo} — " . ($lookupData['message'] ?? $lookupResponse->body()));
                $this->skipped++;

                return;
            }

            $this->info("   ✅ Customer validated for Account No: {$accountNo}");

            // 2. Programme the meter via UBVS
            $programPayload = [
                'meter_number' => $meterNo,
                'account_number' => str_replace(['/', '-'], '', $accountNo),
                'type' => 'disrep',
                'amount' => 1,
            ];

            $this->info("   📡 Sending meter {$meterNo} for programming...");

            $programResponse = Http::withToken($this->programToken)
                ->acceptJson()
                ->timeout(30)
                ->retry(3, 2000, throw: false)
                ->post($this->programUrl, $programPayload);

            $programData = $programResponse->json();

            $this->info("   📥 UBVS Programme Response [{$programResponse->status()}]: " . json_encode($programData));

            Log::info('ProgrammeDirespMeters: UBVS preprogram_meter response', [
                'account_no' => $accountNo,
                'meter_no' => $meterNo,
                'payload' => $programPayload,
                'status' => $programResponse->status(),
                'response' => $programData,
            ]);

            // Treat "already linked" as already-programmed rather than a failure —
            // the meter/account was programmed on a previous run, so we still
            // want to store the record and notify MSMS below.
            $errMessage = (string) ($programData['err_message'] ?? '');
            $alreadyProgrammed = str_contains($errMessage, 'Account already has a meter linked')
                || str_contains($errMessage, 'Meter is already linked to another account')
                || str_contains($errMessage, 'Unlink the currently linked meter first before linking a new one');

            if (!$programResponse->successful() && !$alreadyProgrammed) {
                $errMsg = $programData['err_message'] ?? $programData['message'] ?? $programResponse->body();
                $this->error("   ❌ UBVS PROGRAMMING FAILED for meter {$meterNo}: " . $errMsg);
                $this->failed++;
                $this->errorRows[] = [
                    'meter_no' => $meterNo,
                    'account_no' => $accountNo,
                    'address' => $record['Address'] ?? '',
                    'Region' => $record['Region'] ?? '',
                    'BHub' => $record['BHub'] ?? '',
                    'error_message' => (string) $errMsg,
                ];

                return;
            }

            if ($alreadyProgrammed) {
                $this->warn("   ⚠️  Meter {$meterNo} already linked (programmed previously) — storing record and notifying MSMS anyway");
            } else {
                $this->info("   ✅ Meter {$meterNo} successfully programmed via UBVS");
            }

            // 3. Store the record
            StoreDisrepMeter::create([
                'account_no' => $accountNo,
                'meter_no' => $meterNo,
                'address' => $record['Address'] ?? null,
                'phone' => $record['PhoneNo'] ?? null,
                'date_installed' => $record['DateInstalled'] ?? null,
                'region' => $record['Region'] ?? null,
                'business_hub' => $record['BHub'] ?? null,
            ]);

            $this->info("   💾 Stored DISREP meter record for Account No: {$accountNo}");

            // 4. Notify MSMS that the meter has been programmed
            $notifyPayload = [
                'meter_no' => $meterNo,
                'account_no' => $accountNo,
            ];

            $this->info("   📡 Notifying MSMS that meter {$meterNo} has been programmed...");

            $notifyResponse = Http::withToken($this->msmsToken)
                ->acceptJson()
                ->timeout(30)
                ->retry(3, 2000, throw: false)
                ->post($this->notifyUrl, $notifyPayload);

            $notifyData = $notifyResponse->json();

            $this->info("   📥 MSMS Notify Response [{$notifyResponse->status()}]: " . json_encode($notifyData));

            Log::info('ProgrammeDirespMeters: MSMS disrep/program response', [
                'account_no' => $accountNo,
                'meter_no' => $meterNo,
                'payload' => $notifyPayload,
                'status' => $notifyResponse->status(),
                'response' => $notifyData,
            ]);

            if (!$notifyResponse->successful()) {
                $errMsg = $notifyData['message'] ?? $notifyResponse->body();
                $this->error("   ❌ MSMS NOTIFY FAILED for {$accountNo}: " . $errMsg);
                $this->failed++;
                $this->errorRows[] = [
                    'meter_no' => $meterNo,
                    'account_no' => $accountNo,
                    'address' => $record['Address'] ?? '',
                    'Region' => $record['Region'] ?? '',
                    'BHub' => $record['BHub'] ?? '',
                    'error_message' => (string) $errMsg,
                ];

                return;
            }

            $this->programmed++;

            $this->info("   ✅ Account No {$accountNo} COMPLETE: Meter {$meterNo} programmed, stored, and MSMS notified");
        } catch (\Throwable $e) {
            $this->failed++;

            Log::error('ProgrammeDirespMeters: Exception', [
                'account_no' => $accountNo,
                'meter_no' => $meterNo,
                'message' => $e->getMessage(),
            ]);

            $this->error("   ❌ EXCEPTION while processing {$accountNo}: " . $e->getMessage());
            $this->errorRows[] = [
                'meter_no' => $meterNo,
                'account_no' => $accountNo,
                'address' => $record['Address'] ?? '',
                'error_message' => $e->getMessage(),
            ];
        }
    }
}

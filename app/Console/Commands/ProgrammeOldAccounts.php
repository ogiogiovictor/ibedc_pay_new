<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MIDDLEWARE\MeterAllocation;
use App\Models\MIDDLEWARE\MeterCustomers;
use App\Models\MIDDLEWARE\MeterDetails;
use App\Models\MIDDLEWARE\PaymentRecords;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Models\StoreDisrepMeter;
use Carbon\Carbon;
use App\Models\NAC\UploadHouses;
use App\Mail\OldAccountsErrorReportMail;

class ProgrammeOldAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:programme-old-accounts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected string $programUrl = 'https://ubvs.ibedc.com/api/integration/preprogram_meter';
    protected string $programToken = 'muK2zwbzuZtzwKnCQBvSBHVfu7sDOWf3x0ci4Ekbd4767537';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $allocations = MeterAllocation::with(['customer.payments', 'customer.store', 'meter'])
            ->whereNull('export_date')
            ->whereNotNull('installation_date')
            ->whereHas('customer', function ($q) {
                $q->where('new_setup', '0')->whereRaw('LEN(previous_account_number) >= 12');
            })
            ->whereHas('meter', function ($q) {
                $q->whereNotNull('meter_number');
            })
            ->get();

        $totalAllocations = $allocations->count();
        $totalOutputRows = 0;
        $totalPaymentsAmount = 0;
        $customersWithPayments = 0;
        $customersWithoutPayments = 0;
        $programmed = 0;
        $failed = 0;
        $skipped = 0;
        $errorRows = [];

        foreach ($allocations as $alloc) {
            $customer = $alloc->customer;
            $meter = $alloc->meter;

            $base = [
                'previous_account' => $customer->previous_account_number ?? 'N/A',
                'meter_number' => $meter->meter_number ?? 'N/A',
                'installation_date' => $alloc->installation_date ?? 'N/A',
                'programmed_date' => $alloc->export_date ?? 'NULL',
                'mapid' => $alloc->mapid ?? 'N/A',
                'title' => $customer->title ?? '',
                'surname' => $customer->surname ?? '',
                'firstname' => $customer->firstname ?? '',
                'other_names' => $customer->other_names ?? '',
                'service_center' => $customer->service_center ?? '',
                'city' => $customer->city ?? '',
                'hub' => $customer->hub ?? '',
                'location' => $customer->store->location ?? '',

            ];

            if ($customer->payments && $customer->payments->isNotEmpty()) {
                $customersWithPayments++;
                foreach ($customer->payments as $py) {
                    $amount = is_numeric($py->amount) ? (float) $py->amount : 0;
                    $totalPaymentsAmount += $amount;
                    $totalOutputRows++;
                    $this->info("previous_account: {$base['previous_account']}, meter_number: {$base['meter_number']}, installation_date: {$base['installation_date']}, programmed_date: {$base['programmed_date']}, mapid: {$base['mapid']}, payment: " . ($py->amount ?? 'N/A') . ", name: {$base['title']} {$base['surname']} {$base['firstname']} {$base['other_names']}");
                }
            } else {
                $customersWithoutPayments++;
                $totalOutputRows++;
                $this->info("previous_account: {$base['previous_account']}, meter_number: {$base['meter_number']}, installation_date: {$base['installation_date']}, programmed_date: {$base['programmed_date']}, mapid: {$base['mapid']}, payment: N/A, name: {$base['title']} {$base['surname']} {$base['firstname']} {$base['other_names']}");
            }
            // Business logic: check UploadHouses, then attempt to programme via UBVS and update MSMS
            try {
                $prevAccountRaw = trim((string) ($customer->previous_account_number ?? ''));
                if (!empty($prevAccountRaw) && UploadHouses::where('account_no', $prevAccountRaw)->exists()) {
                    $this->warn("   ⚠️  SKIPPING programming for mapid={$alloc->mapid}: account exists in UploadHouses ({$prevAccountRaw})");
                    $skipped = isset($skipped) ? $skipped + 1 : 1;
                    continue;
                }
                $meterNo = $meter->meter_number ?? null;
                $accountNo = preg_replace('/[\/\-]/', '', $customer->previous_account_number ?? '');

                $paymentAmount = $customer->payments->sum('amount') ?: 0;

                $programPayload = [
                    'meter_number' => $meterNo,
                    'account_number' => $accountNo,
                    'type' => 'map',
                    'amount' => $paymentAmount,
                ];

                $this->info("   📡 Sending meter {$meterNo} for programming (account: {$accountNo})...");
                $this->info('   📤 UBVS payload: ' . json_encode($programPayload, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));

                $programResponse = Http::withToken($this->programToken)
                    ->acceptJson()
                    ->timeout(30)
                    ->retry(3, 2000, throw: false)
                    ->post($this->programUrl, $programPayload);

                $programData = $programResponse->json();

                $this->info("   📥 UBVS Programme Response [{$programResponse->status()}]: " . json_encode($programData));

                $errMessage = (string) ($programData['err_message'] ?? '');
                $alreadyProgrammed = str_contains($errMessage, 'Account already has a meter linked')
                    || str_contains($errMessage, 'Meter is already linked to another account')
                    || str_contains($errMessage, 'Unlink the currently linked meter first before linking a new one');
                   // || str_contains($errMessage, 'Cannot link a prepaid meter to a prepaid account. Unlink the current meter');

                if (!$programResponse->successful() && !$alreadyProgrammed) {
                    $errMsg = $programData['err_message'] ?? 'Unknown error';
                    $this->error("   ❌ UBVS PROGRAMMING FAILED for meter {$meterNo}: " . $errMsg);
                    $failed++;
                    $errorRows[] = [
                        'meter_no' => $base['meter_number'],
                        'account_no' => $base['previous_account'],
                        'customer_name' => trim("{$base['title']} {$base['surname']} {$base['firstname']} {$base['other_names']}"),
                        'service_center' => $base['service_center'],
                        'city' => $base['city'],
                        'location' => $base['location'],
                        'error_message' => (string) $errMsg,
                    ];
                } else {
                    // Create StoreDisrepMeter record
                    StoreDisrepMeter::create([
                        'account_no' => $accountNo,
                        'meter_no' => $meterNo,
                        'address' => $prevAccountRaw ?: null,
                        'phone' => $prevAccountRaw ?: null,
                        'date_installed' => Carbon::now(),
                        'region' => "old_accounts",
                        'business_hub' => "old_accounts",
                    ]);

                    // Update customer progress to 6
                    $customer->progress = 6;
                    $customer->save();

                    // Update allocation export_by and export_date
                    $alloc->export_by = 6;
                    $alloc->export_date = Carbon::now();
                    $alloc->save();

                    $programmed++;
                    $this->info("   ✅ Processed allocation mapid={$alloc->mapid} — stored and MSMS updated");
                }
            } catch (\Throwable $e) {
                $failed++;
                $this->error("   ❌ EXCEPTION while processing allocation mapid={$alloc->mapid}: " . $e->getMessage());
                $errorRows[] = [
                    'meter_no' => $base['meter_number'],
                    'account_no' => $base['previous_account'],
                    'customer_name' => trim("{$base['title']} {$base['surname']} {$base['firstname']} {$base['other_names']}"),
                    'service_center' => $base['service_center'],
                    'city' => $base['city'],
                    'location' => $base['location'],
                    'error_message' => $e->getMessage(),
                ];
            }
        }

        // Summary
        $this->info('=== Summary ===');
        $this->info("Total matched allocations: {$totalAllocations}");
        $this->info("Total output rows (one per payment or one if none): {$totalOutputRows}");
        $this->info("Customers with payments: {$customersWithPayments}");
        $this->info("Customers without payments: {$customersWithoutPayments}");
        $this->info("Total payments amount: " . number_format($totalPaymentsAmount, 2));
        $this->info("Programmed allocations: {$programmed}");
        $this->info("Skipped allocations (already in UploadHouses): {$skipped}");
        $this->info("Failed allocations: {$failed}");

        if (!empty($errorRows)) {
            try {
               // Mail::mailer('alerts')->to('Basirat.Opoola@ibedc.com')
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
                    ->send(new OldAccountsErrorReportMail($errorRows));
                $this->info("📧 Error report email queued for " . count($errorRows) . " failed allocation(s).");
            } catch (\Throwable $e) {
                $this->error("   ❌ Failed to send error report email: " . $e->getMessage());
            }
        }

        return 0;
    }
}

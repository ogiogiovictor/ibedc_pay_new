<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\NAC\UBVSCustomers;

class GetUBVSCustomerRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-ubvs-customer-records
                            {--start-date= : Start date in Y-m-d format}
                            {--end-date= : End date in Y-m-d format}
                            {--account-number= : Filter by account number}
                            {--meter-number= : Filter by meter number}
                            {--region= : Filter by region}
                            {--business-hub= : Filter by business hub}
                            {--account-type= : Filter by account type}
                            {--feeder-code= : Filter by feeder code}
                            {--dss-code= : Filter by DSS code}
                            {--account-status= : Filter by account status}
                            {--per-page=30 : Number of records per page}
                            {--fresh : Ignore any saved progress and restart from page 1}';

    /**
     * The console command description.
     *
     * # Default (per_page=30, all pages)
     * php artisan app:get-ubvs-customer-records
     *
     * # With optional filters
     * php artisan app:get-ubvs-customer-records --start-date=2026-05-03 --end-date=2026-05-03 --region=KWARA --business-hub=Challenge --account-status=Active --per-page=50
     *
     * # Add to scheduler (app/Console/Kernel.php)
     * $schedule->command('app:get-ubvs-customer-records')->daily();
     *
     * # If a run stops early (e.g. an error), re-running with no flags resumes
     * # from the page it stopped on. Pass --fresh to ignore that and start over.
     * php artisan app:get-ubvs-customer-records --fresh
     *
     * @var string
     */
    protected $description = 'Fetch UBVS customers and store new/updated records';

    /**
     * API Base URL
     */
    protected string $apiUrl = 'https://ubvs.ibedc.com/api/integration/customers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $perPage = (int) ($this->option('per-page') ?: 30);

        $query = array_filter([
            'start_date'      => $this->option('start-date'),
            'end_date'        => $this->option('end-date'),
            'account_number'  => $this->option('account-number'),
            'meter_number'    => $this->option('meter-number'),
            'region'          => $this->option('region'),
            'business_hub'    => $this->option('business-hub'),
            'account_type'    => $this->option('account-type'),
            'feeder_code'     => $this->option('feeder-code'),
            'dss_code'        => $this->option('dss-code'),
            'account_status'  => $this->option('account-status'),
        ], fn ($value) => !is_null($value) && $value !== '');

        $query['per_page'] = $perPage;

        if ($this->option('fresh')) {
            $this->clearProgress();
            $this->info('--fresh given: ignoring any saved progress, starting from page 1.');
        }

        $this->info('Fetching UBVS customers...');

        $this->fetchCustomers($query);

        $this->info('UBVS customer sync completed successfully.');

        return Command::SUCCESS;
    }

    /**
     * Fetch customers with pagination, resuming from the last saved page if present
     */
    protected function fetchCustomers(array $baseQuery): void
    {
        $page = 1;

        $progress = $this->loadProgress();

        if ($progress && $progress['query'] === $baseQuery) {
            $page = $progress['page'];
            $this->info("Resuming from page {$page} (previous run stopped here). Use --fresh to restart from the beginning.");
        }

        $hasMorePages = true;

        while ($hasMorePages) {
            $this->info("Fetching page {$page}...");

            try {
                $query = array_merge($baseQuery, ['page' => $page]);

                $response = Http::withToken('muK2zwbzuZtzwKnCQBvSBHVfu7sDOWf3x0ci4Ekbd4767537')
                    ->acceptJson()
                    ->timeout(60)
                    ->retry(3, 5000)
                    ->get($this->apiUrl, $query);

                if (!$response->successful()) {
                    $this->error("API request failed with status: {$response->status()}");
                    $this->warn("Progress saved. Re-run the command to resume from page {$page}, or pass --fresh to restart from the beginning.");
                    return;
                }

                $data = $response->json();

                if (!isset($data['response']['payload']['customers'])) {
                    $this->warn('No customers found in response');
                    $this->warn("Progress saved. Re-run the command to resume from page {$page}, or pass --fresh to restart from the beginning.");
                    return;
                }

                $customers = $data['response']['payload']['customers'];
                $pagination = $data['pagination'] ?? [];

                if (empty($customers)) {
                    $hasMorePages = false;
                    continue;
                }

                $this->info(sprintf(
                    'Page %s/%s: %s customers received (total: %s)',
                    $pagination['current_page'] ?? $page,
                    $pagination['last_page'] ?? 1,
                    count($customers),
                    $pagination['total'] ?? 'unknown'
                ));

                $this->processCustomers($customers);

                $currentPage = $pagination['current_page'] ?? $page;
                $lastPage = $pagination['last_page'] ?? 1;
                $hasMorePages = $currentPage < $lastPage;
                $page++;

                if ($hasMorePages) {
                    $this->saveProgress($baseQuery, $page);
                    usleep(250000); // 250ms delay
                }

            } catch (\Exception $e) {
                $this->error("Error fetching page {$page}: " . $e->getMessage());
                $this->warn("Progress saved. Re-run the command to resume from page {$page}, or pass --fresh to restart from the beginning.");
                return;
            }
        }

        $this->clearProgress();
    }

    /**
     * Read saved sync progress (query filters + next page to fetch), if any
     */
    protected function loadProgress(): ?array
    {
        $path = $this->progressPath();

        if (!file_exists($path)) {
            return null;
        }

        $data = json_decode(file_get_contents($path), true);

        return is_array($data) && isset($data['query'], $data['page']) ? $data : null;
    }

    /**
     * Save the next page to fetch for the given query filters
     */
    protected function saveProgress(array $query, int $page): void
    {
        file_put_contents($this->progressPath(), json_encode([
            'query' => $query,
            'page' => $page,
        ]));
    }

    /**
     * Clear saved progress
     */
    protected function clearProgress(): void
    {
        $path = $this->progressPath();

        if (file_exists($path)) {
            unlink($path);
        }
    }

    protected function progressPath(): string
    {
        return storage_path('app/ubvs_sync_progress.json');
    }

    /**
     * Process and upsert customers, skipping unchanged ones
     */
    protected function processCustomers(array $customers): void
    {
        $inserted = 0;
        $updated = 0;
        $skipped = 0;

        $accountNumbers = array_column($customers, 'account_number');

        $existingCustomers = UBVSCustomers::whereIn('account_number', $accountNumbers)
            ->get()
            ->keyBy('account_number');

        $recordsToInsert = [];

        foreach ($customers as $customer) {
            $accountNumber = $customer['account_number'] ?? null;

            if (!$accountNumber) {
                $this->warn('Customer without account_number skipped');
                $skipped++;
                continue;
            }

            $record = [
                'account_number' => $accountNumber,
                'meter_number' => $customer['meter_number'] ?? null,
                'nin' => $customer['nin'] ?? null,
                'surname' => $customer['surname'] ?? null,
                'firstname' => $customer['firstname'] ?? null,
                'othername' => $customer['othername'] ?? null,
                'address' => $customer['address'] ?? null,
                'city' => $customer['city'] ?? null,
                'state' => $customer['state'] ?? null,
                'lga' => $customer['lga'] ?? null,
                'service_center' => $customer['service_center'] ?? null,
                'service_code' => $customer['service_code'] ?? null,
                'email' => $customer['email'] ?? null,
                'tariff' => $customer['tariff'] ?? null,
                'arrears' => $customer['arrears'] ?? null,
                'multiplier' => $customer['multiplier'] ?? null,
                'phone_number' => $customer['phone_number'] ?? null,
                'account_type' => $customer['account_type'] ?? null,
                'customer_type' => $customer['customer_type'] ?? null,
                'premise_type' => $customer['premise_type'] ?? null,
                'date_added' => $customer['date_added'] ?? null,
                'latitude' => $customer['latitude'] ?? null,
                'longitude' => $customer['longitude'] ?? null,
                'dss_code' => $customer['dss_code'] ?? null,
                'feeder_code' => $customer['feeder_code'] ?? null,
                'status' => $customer['status'] ?? null,
                'adc' => $customer['adc'] ?? null,
                'stored_average' => $customer['stored_average'] ?? null,
                'business_hub' => $customer['business_hub'] ?? null,
                'region' => $customer['region'] ?? null,
            ];

            if ($existingCustomers->has($accountNumber)) {
                UBVSCustomers::where('account_number', $accountNumber)->update($record);
                $this->info("  ↻ Updated customer: {$accountNumber} - {$record['surname']} {$record['firstname']}");
                $updated++;
                continue;
            }

            $recordsToInsert[] = $record;
            $this->info("  + New customer queued: {$accountNumber} - {$record['surname']} {$record['firstname']}");
            $inserted++;
        }

        if (!empty($recordsToInsert)) {
            // foreach (array_chunk($recordsToInsert, 500) as $chunk) {
            //     UBVSCustomers::insert($chunk);
            // }

            foreach ($recordsToInsert as $record) {
                UBVSCustomers::create($record);
            }
        }

        $this->info("Processed: {$inserted} inserted, {$updated} updated, {$skipped} skipped");
    }
}

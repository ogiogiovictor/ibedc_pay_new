<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\NAC\UBVSGetCustomers;
use Carbon\Carbon;

class UBVSCustomerpull extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-customerpull
                            {--start-date= : Start date in Y-m-d format}
                            {--end-date= : End date in Y-m-d format}
                            {--fresh : Restart from beginning}';

    protected $description = 'Fetch UBVS transactions weekly and store new records';

    protected string $apiUrl = 'https://ubvs.ibedc.com/api/integration/transactions';

    /**
     * Cache key
     */
    protected string $progressKey = 'ubvs_customer_sync_progress';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        /*
        |--------------------------------------------------------------------------
        | Reset Progress
        |--------------------------------------------------------------------------
        */
        if ($this->option('fresh')) {
            Cache::forget($this->progressKey);
            $this->warn('♻️ Progress reset. Starting from beginning...');
        }

        $startDate = $this->option('start-date')
            ? Carbon::parse($this->option('start-date'))->startOfDay()
            : Carbon::now()->subWeek()->startOfDay();

        $endDate = $this->option('end-date')
            ? Carbon::parse($this->option('end-date'))->endOfDay()
            : Carbon::now()->endOfDay();

        /*
        |--------------------------------------------------------------------------
        | Resume Saved Progress (only if no dates explicitly provided)
        |--------------------------------------------------------------------------
        */
        $savedProgress = Cache::get($this->progressKey);

        if (!$this->option('start-date') && !$this->option('end-date') && $savedProgress) {
            $cachedStart = Carbon::parse($savedProgress['current_start']);
            
            // Only resume if cached start is still within our target window
            if ($cachedStart->lte($endDate)) {
                $startDate = $cachedStart;
                $this->info("🔁 Resuming from saved progress...");
            } else {
                $this->warn("⚠️ Cached progress ({$cachedStart->format('Y-m-d')}) is past end date. Starting fresh.");
                Cache::forget($this->progressKey);
            }
        }

        $this->info(
            "Fetching UBVS transactions from {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}"
        );

        $currentStart = $startDate->copy();

        while ($currentStart->lte($endDate)) {

            $currentEnd = $currentStart
                ->copy()
                ->addWeek()
                ->subSecond()
                ->min($endDate);

            $this->info(
                "📅 Processing week: {$currentStart->format('Y-m-d')} to {$currentEnd->format('Y-m-d')}"
            );

            $this->fetchWeeklyTransactions(
                $currentStart->format('Y-m-d H:i:s'),
                $currentEnd->format('Y-m-d H:i:s')
            );

            /*
            |--------------------------------------------------------------------------
            | Save Week Progress
            |--------------------------------------------------------------------------
            */
            Cache::put($this->progressKey, [
                'current_start' => $currentStart->copy()->addWeek()->toDateTimeString(),
                'page' => 1,
            ], now()->addDays(7));

            $currentStart->addWeek();
        }

        $this->info('✅ UBVS transaction sync completed successfully.');

        return Command::SUCCESS;
    }

    /**
     * Fetch weekly transactions with resume support
     */
    protected function fetchWeeklyTransactions(string $startDate, string $endDate): void
    {
        $savedProgress = Cache::get($this->progressKey);

        $page = $savedProgress['page'] ?? 1;

        $afterId = $savedProgress['after_id'] ?? null;

        $useCursorPagination = $savedProgress['use_cursor'] ?? false;

        $hasMorePages = true;

        while ($hasMorePages) {

            if ($useCursorPagination) {
                $this->info("📄 Fetching cursor page after_id={$afterId}...");
            } else {
                $this->info("📄 Fetching page {$page}...");
            }

            try {

                $query = [
                    'start_date' => date('Ymd', strtotime($startDate)),
                    'end_date'   => date('Ymd', strtotime($endDate)),
                    'per_page'   => 100,
                ];

                if ($useCursorPagination) {
                    $query['after_id'] = $afterId;
                } else {
                    $query['page'] = $page;
                }

                $response = Http::withToken('muK2zwbzuZtzwKnCQBvSBHVfu7sDOWf3x0ci4Ekbd4767537')
                    ->acceptJson()
                    ->timeout(180)
                    ->retry(3, 5000)
                    ->get($this->apiUrl, $query);

                if (!$response->successful()) {

                    $this->error(
                        "❌ API request failed with status: {$response->status()}"
                    );

                    break;
                }

                $data = $response->json();

                if (!isset($data['response']['payload']['transactions'])) {

                    $this->warn('⚠️ No transactions found.');

                    break;
                }

                $transactions = $data['response']['payload']['transactions'];

                $pagination = $data['response']['payload']['pagination'] ?? [];

                if (empty($transactions)) {

                    $hasMorePages = false;

                    continue;
                }

                $this->processTransactions($transactions);

                if (array_key_exists('has_more', $pagination) || array_key_exists('next_after_id', $pagination)) {

                    $useCursorPagination = true;

                    $hasMorePages = (bool) ($pagination['has_more'] ?? false);

                    $afterId = $pagination['next_after_id'] ?? null;

                    if ($hasMorePages && $afterId === null) {
                        $this->warn('⚠️ Pagination indicates more items but missing next_after_id. Stopping fetch.');
                        $hasMorePages = false;
                    }
                } else {

                    $currentPage = $pagination['current_page'] ?? $page;

                    $lastPage = $pagination['last_page'] ?? 1;

                    $hasMorePages = $currentPage < $lastPage;

                    $page++;
                }

                /*
                |--------------------------------------------------------------------------
                | SAVE PAGE PROGRESS
                |--------------------------------------------------------------------------
                */
                Cache::put($this->progressKey, [
                    'current_start' => $startDate,
                    'page' => $page,
                    'after_id' => $afterId,
                    'use_cursor' => $useCursorPagination,
                ], now()->addDays(7));

                if ($hasMorePages) {

                    usleep(250000);
                }

            } catch (\Throwable $e) {

                $this->error(
                    "❌ Error fetching page {$page}: {$e->getMessage()}"
                );

                // Progress already saved
                // Next rerun resumes automatically

                break;
            }
        }
    }

    /**
     * Insert transactions
     */
    protected function processTransactions(array $transactions): void
    {
        $inserted = 0;

        $skipped = 0;

        $paymentReferences = array_column(
            $transactions,
            'payment_reference'
        );

        $existingReferences = UBVSGetCustomers::whereIn(
            'payment_reference',
            $paymentReferences
        )
        ->pluck('payment_reference')
        ->toArray();

        $existingSet = array_flip($existingReferences);

        $recordsToInsert = [];

        foreach ($transactions as $transaction) {

            $paymentRef = $transaction['payment_reference'] ?? null;

            if (!$paymentRef) {

                $skipped++;

                continue;
            }

            if (isset($existingSet[$paymentRef])) {

                $skipped++;

                continue;
            }

            $recordsToInsert[] = [
                'payment_reference' => $paymentRef,
                'timestamp' => $transaction['timestamp'] ?? null,
                'amount_tendered' => $transaction['amount_tendered'] ?? null,
                'deduction' => $transaction['deduction'] ?? null,
                'vat' => $transaction['vat'] ?? null,
                'cost_of_units' => $transaction['cost_of_units'] ?? null,
                'energy_consumed' => $transaction['energy_consumed'] ?? null,
                'meter_number' => $transaction['meter_number'] ?? null,
                'aggregator_name' => $transaction['aggregator_name'] ?? null,
                'token_credit' => isset($transaction['token'])
                    ? json_encode($transaction['token'])
                    : null,
                'business_hub' => $transaction['business_hub'] ?? null,
                'service_unit' => $transaction['service_unit'] ?? null,
                'transaction_status' => $transaction['transaction_status'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
                'account_no' => $transaction['account_no'] ?? null,
                'pay_method' => $transaction['pay_method'] ?? null,
                'cust_name' => $transaction['cust_name'] ?? null,
                'service_band' => $transaction['service_band'] ?? null,
                'dss_name' => $transaction['dss_name'] ?? null,
                'feeder_name' => $transaction['feeder_name'] ?? null,
                'payment_channel' => $transaction['payment_channel'] ?? null,
                'region' => $transaction['region'] ?? null,
                'tariff' => $transaction['tariff'] ?? null,
                'unit_bought' => $transaction['unit_bought'] ?? null,
                'multipler' => $transaction['multipler'] ?? null,
                'pay_type' => $transaction['pay_type'] ?? null,
                'feederid' => $transaction['feederid'] ?? null,
                'dssid' => $transaction['dssid'] ?? null,
            ];

            $inserted++;
        }

        if (!empty($recordsToInsert)) {

            foreach (array_chunk($recordsToInsert, 50) as $chunk) {

                UBVSGetCustomers::insert($chunk);
            }
        }

        $this->info(
            "✅ Inserted: {$inserted}, Skipped: {$skipped}"
        );
    }

    
}
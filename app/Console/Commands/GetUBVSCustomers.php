<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\NAC\UBVSGetCustomers;
use Carbon\Carbon;

class GetUBVSCustomers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
   protected $signature = 'app:get-ubvs-customers 
                            {--start-date= : Start date in Y-m-d format}
                            {--end-date= : End date in Y-m-d format}';


    /**
     * The console command description.
     *# Default: last week to now
    * php artisan app:get-ubvs-customers

     *   # Specific date range
      *  php artisan app:get-ubvs-customers --start-date=2026-05-03 --end-date=2026-05-03

       * # Add to scheduler (app/Console/Kernel.php)
      *  $schedule->command('app:get-ubvs-customers')->weekly();
     * @var string
     */
    protected $description = 'Fetch UBVS transactions weekly and store new records';


     /**
     * API Base URL
     */
    protected string $apiUrl = 'https://ubvs.ibedc.com/api/integration/transactions';

    /**
     * Execute the console command.
     */
   public function handle()
    {
        $startDate = $this->option('start-date') 
            ? Carbon::parse($this->option('start-date'))->startOfDay()
            : Carbon::now()->subWeek()->startOfDay();
            
        $endDate = $this->option('end-date')
            ? Carbon::parse($this->option('end-date'))->endOfDay()
            : Carbon::now()->endOfDay();

        $this->info("Fetching UBVS transactions from {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");

        // Process in weekly chunks
        $currentStart = $startDate->copy();
        
        while ($currentStart->lte($endDate)) {
            $currentEnd = $currentStart->copy()->addWeek()->subSecond()->min($endDate);
            
            $this->info("Processing week: {$currentStart->format('Y-m-d')} to {$currentEnd->format('Y-m-d')}");
            
            $this->fetchWeeklyTransactions(
                $currentStart->format('Y-m-d H:i:s'),
                $currentEnd->format('Y-m-d H:i:s')
            );
            
            $currentStart->addWeek();
        }

        $this->info('UBVS transaction sync completed successfully.');
        
        return Command::SUCCESS;
    }




      /**
     * Fetch transactions for a specific week with pagination
     */
    protected function fetchWeeklyTransactions(string $startDate, string $endDate): void
    {
        $page = 1;
        $afterId = null;
        $useCursorPagination = false;
        $hasMorePages = true;

        while ($hasMorePages) {
            if ($useCursorPagination) {
                $this->info("Fetching cursor page after_id={$afterId}...");
            } else {
                $this->info("Fetching page {$page}...");
            }
            
            try {

                $query = [
                    'start_date' => date('Ymd', strtotime($startDate)),
                    'end_date'   => date('Ymd', strtotime($endDate)),
                    'per_page'   => 30,
                ];

                if ($useCursorPagination) {
                    $query['after_id'] = $afterId;
                } else {
                    $query['page'] = $page;
                }

                $response = Http::withToken('muK2zwbzuZtzwKnCQBvSBHVfu7sDOWf3x0ci4Ekbd4767537')
                    ->acceptJson()
                    ->timeout(60)
                    ->get($this->apiUrl, $query);

                // $response = Http::withToken('rp6TAHm8rX16Mw6FI2K4zWkTQVyMqoJ52lVoFjNjdda7e7fa')
                //     ->timeout(60)
                //     ->get($this->apiUrl, [
                //         'start_date' => date('Ymd', strtotime($startDate)),
                //         'end_date' => date('Ymd', strtotime($endDate)),
                //         'page' => $page,
                //         'per_page' => 30,
                //     ]);


                $this->info('Date: ' . $startDate);
                $this->info('Body' . $response->body());

                if (!$response->successful()) {
                    $this->error("API request failed with status: {$response->status()}");
                    break;
                }

                $data = $response->json();

                if (!isset($data['response']['payload']['transactions'])) {
                    $this->warn('No transactions found in response');
                    break;
                }

                $transactions = $data['response']['payload']['transactions'];
                $pagination = $data['pagination'] ?? [];

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

                if ($hasMorePages) {
                    usleep(250000); // 250ms delay
                }

            } catch (\Exception $e) {
                $this->error("Error fetching page {$page}: " . $e->getMessage());
                break;
            }
        }
    

        
    }



     /**
     * Process and insert transactions, skipping existing ones
     */
    protected function processTransactions(array $transactions): void
    {
        $inserted = 0;
        $skipped = 0;

        // Get all payment references from this batch to check existence in bulk
        $paymentReferences = array_column($transactions, 'payment_reference');
        
        // Check which already exist in database
        $existingReferences = UBVSGetCustomers::whereIn('payment_reference', $paymentReferences)
            ->pluck('payment_reference')
            ->toArray();

        $existingSet = array_flip($existingReferences);

        $recordsToInsert = [];

        foreach ($transactions as $transaction) {
            $paymentRef = $transaction['payment_reference'] ?? null;

            if (!$paymentRef) {
                $this->warn('Transaction without payment_reference skipped');
                $skipped++;
                continue;
            }

            // Skip if already exists
            if (isset($existingSet[$paymentRef])) {
                $skipped++;
                continue;
            }

            // Prepare record for insert
            $recordsToInsert[] = [
                'payment_reference' => $paymentRef,
                'timestamp' => $transaction['timestamp'] ?? now(),
                'amount_tendered' => $transaction['amount_tendered'] ?? null,
                'deduction' => $transaction['deduction'] ?? null,
                'vat' => $transaction['vat'] ?? null,
                'cost_of_units' => $transaction['cost_of_units'] ?? null,
                'energy_consumed' => $transaction['energy_consumed'] ?? null,
                'meter_number' => $transaction['meter_number'] ?? null,
                'aggregator_name' => $transaction['aggregator_name'] ?? null,
                'token_credit' => isset($transaction['token']) ? json_encode($transaction['token']) : null,
                'business_hub' => $transaction['business_hub'] ?? null,
                'service_unit' => $transaction['service_unit'] ?? null,
                'transaction_status' => $transaction['transaction_status'] ?? null,
                'created_at' => $transaction['created_at'] ?? now(),
                'updated_at' => $transaction['updated_at'] ?? now(),
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

        // Bulk insert new records
        if (!empty($recordsToInsert)) {
            // Use chunking for large batches to avoid SQL parameter limits
            foreach (array_chunk($recordsToInsert, 500) as $chunk) {
                UBVSGetCustomers::insert($chunk);
            }
        }

        $this->info("Processed: {$inserted} inserted, {$skipped} skipped (already exist)");
    }


}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\NAC\UBVSFeeders;

class GetUBVSFeeders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-ubvs-feeders
                            {--start-date= : Start date in Y-m-d format}
                            {--end-date= : End date in Y-m-d format}
                            {--name= : Filter by feeder name}
                            {--code= : Filter by feeder code}
                            {--per-page=30 : Number of records per page}';

    /**
     * The console command description.
     *
     * # Default (per_page=30, all pages)
     * php artisan app:get-ubvs-feeders
     *
     * # With optional filters
     * php artisan app:get-ubvs-feeders --start-date=2026-05-03 --end-date=2026-05-03 --name=AGODI --code=ACE102033220171017201012874 --per-page=50
     *
     * # Add to scheduler (app/Console/Kernel.php)
     * $schedule->command('app:get-ubvs-feeders')->daily();
     *
     * @var string
     */
    protected $description = 'Fetch UBVS feeders and store new/updated records';

    /**
     * API Base URL
     */
    protected string $apiUrl = 'https://ubvs.ibedc.com/api/integration/feeders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $perPage = (int) ($this->option('per-page') ?: 30);

        $query = array_filter([
            'start_date' => $this->option('start-date'),
            'end_date'   => $this->option('end-date'),
            'name'       => $this->option('name'),
            'code'       => $this->option('code'),
        ], fn ($value) => !is_null($value) && $value !== '');

        $query['per_page'] = $perPage;

        $this->info('Fetching UBVS feeders...');

        $this->fetchFeeders($query);

        $this->info('UBVS feeder sync completed successfully.');

        return Command::SUCCESS;
    }

    /**
     * Fetch feeders with pagination
     */
    protected function fetchFeeders(array $baseQuery): void
    {
        $page = 1;
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
                    break;
                }

                $data = $response->json();

                if (!isset($data['response']['payload']['feeders'])) {
                    $this->warn('No feeders found in response');
                    break;
                }

                $feeders = $data['response']['payload']['feeders'];
                $pagination = $data['pagination'] ?? [];

                if (empty($feeders)) {
                    $hasMorePages = false;
                    continue;
                }

                $this->info(sprintf(
                    'Page %s/%s: %s feeders received (total: %s)',
                    $pagination['current_page'] ?? $page,
                    $pagination['last_page'] ?? 1,
                    count($feeders),
                    $pagination['total'] ?? 'unknown'
                ));

                $this->processFeeders($feeders);

                $currentPage = $pagination['current_page'] ?? $page;
                $lastPage = $pagination['last_page'] ?? 1;
                $hasMorePages = $currentPage < $lastPage;
                $page++;

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
     * Process and upsert feeders, skipping unchanged ones
     */
    protected function processFeeders(array $feeders): void
    {
        $inserted = 0;
        $updated = 0;
        $skipped = 0;

        $codes = array_column($feeders, 'code');

        $existingFeeders = UBVSFeeders::whereIn('code', $codes)
            ->get()
            ->keyBy('code');

        $recordsToInsert = [];

        foreach ($feeders as $feeder) {
            $code = $feeder['code'] ?? null;

            if (!$code) {
                $this->warn('Feeder without code skipped');
                $skipped++;
                continue;
            }

            $record = [
                'code' => $code,
                'name' => $feeder['name'] ?? null,
                'technical_name' => $feeder['technical_name'] ?? null,
                'service_band' => $feeder['service_band'] ?? null,
                'asset_type' => $feeder['asset_type'] ?? null,
                'business_hub' => $feeder['business_hub'] ?? null,
                'region' => $feeder['region'] ?? null,
                'latitude' => $feeder['latitude'] ?? null,
                'longitude' => $feeder['longitude'] ?? null,
            ];

            if ($existingFeeders->has($code)) {
                UBVSFeeders::where('code', $code)->update($record);
                $this->info("  ↻ Updated feeder: {$code} - {$record['name']}");
                $updated++;
                continue;
            }

            $recordsToInsert[] = $record;
            $this->info("  + New feeder queued: {$code} - {$record['name']}");
            $inserted++;
        }

        if (!empty($recordsToInsert)) {
            foreach (array_chunk($recordsToInsert, 500) as $chunk) {
                UBVSFeeders::insert($chunk);
            }
        }

        $this->info("Processed: {$inserted} inserted, {$updated} updated, {$skipped} skipped");
    }
}

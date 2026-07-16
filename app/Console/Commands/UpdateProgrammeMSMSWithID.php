<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NAC\UploadHouses;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateProgrammeMSMSWithID extends Command
{
    /**
     * Example:
     * php artisan app:resync-msms-meters 11195
     * php artisan app:resync-msms-meters 11195,11196,11197
     *
     * @var string
     */
    protected $signature = 'app:resync-msms-meters {ids}';

    /**
     * @var string
     */
    protected $description = 'Resync MSMS meters based on provided UploadHouse IDs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("🚀 Starting MSMS Update Process...");

        // Convert comma-separated IDs into array
        $ids = array_filter(
            array_map('trim', explode(',', $this->argument('ids')))
        );

        if (empty($ids)) {

            $this->error("❌ No valid IDs supplied.");

            return Command::FAILURE;
        }

        $this->info("📌 Processing IDs: " . implode(', ', $ids));

        // Build query
        $query = UploadHouses::with(['landlordinfo', 'account'])
            ->whereIn('id', $ids)
            ->whereNotNull('map_id')
            ->whereNotNull('account_no');

        // Count matching records
        $count = $query->count();

        $this->info("📊 Total Records Found: {$count}");

        // If no record found
        if ($count === 0) {

            foreach ($ids as $id) {

                $record = UploadHouses::find($id);

                if (!$record) {

                    $this->error("❌ Record with ID {$id} not found.");

                    continue;
                }

                $this->error("❌ Record ID {$id} exists but failed query conditions.");

                $this->line("------------------------------------------------");

                $this->line("ID: " . $record->id);
                $this->line("MAP ID: " . ($record->map_id ?? 'NULL'));
                $this->line("ACCOUNT NO: " . ($record->account_no ?? 'NULL'));
                $this->line("SERVICE CLASS: " . ($record->service_class ?? 'NULL'));
                $this->line("PAID FOR METER: " . ($record->paid_for_meter ?? 'NULL'));

                $this->line("------------------------------------------------");
            }

            return Command::FAILURE;
        }

        // Process records
        $query->chunk(50, function ($houses) {

            foreach ($houses as $house) {

                $this->info("➡️ Processing ID: {$house->id}");

                try {

                    $payload = [
                        "tracking_id" => $house->id,
                        "map_id"      => $house->map_id,
                        "account_no"  => $house->account_no,
                    ];

                    $this->info("📡 Payload: " . json_encode($payload));

                    $response = Http::withToken('LIVEKEY_0XJLDYJZOQWF8UQ9XWVTH')
                        ->timeout(60)
                        ->post(
                            'https://msms.ibedc.com/api/v2/setup/update',
                            $payload
                        );

                    $this->info("📡 Status: " . $response->status());

                    $this->info("📡 Response: " . $response->body());

                    Log::info('MSMS Request', [
                        'payload'  => $payload,
                        'status'   => $response->status(),
                        'response' => $response->json(),
                    ]);

                    if ($response->successful()) {

                        $house->update([
                            'service_class' => 'msms_updated',
                        ]);

                        $this->info(
                            "✅ Successfully updated ID: {$house->id}"
                        );

                    } else {

                        $this->error(
                            "❌ Failed updating ID: {$house->id}"
                        );

                        Log::error('MSMS Failed Response', [
                            'id'       => $house->id,
                            'payload'  => $payload,
                            'status'   => $response->status(),
                            'response' => $response->body(),
                        ]);
                    }

                } catch (\Exception $e) {

                    Log::error('MSMS Exception', [
                        'id'      => $house->id,
                        'message' => $e->getMessage(),
                    ]);

                    $this->error(
                        "❌ Exception for ID {$house->id}: " . $e->getMessage()
                    );
                }
            }
        });

        $this->info("✅ MSMS Update Completed.");

        return Command::SUCCESS;
    }
}
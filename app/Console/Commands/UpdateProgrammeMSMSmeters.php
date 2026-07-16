<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NAC\UploadHouses;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;


class UpdateProgrammeMSMSmeters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-msms-meters';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update MSMS meters based on IBEDC API data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
         $this->info("🚀 Starting MSMS Update Process...");

        UploadHouses::with(['landlordinfo', 'account'])
          // ->whereIN('id', ['118142'])
           ->whereNull('service_class')
            ->whereNotNull('map_id')
            ->whereRaw("LOWER(TRIM(paid_for_meter)) = ?", ['Yes']) // ✅ corrected // should be Yes, Old
            ->whereNotNull('account_no') // ✅ important based on your requirement
            ->chunk(50, function ($houses) {

                foreach ($houses as $house) {

                    $this->info("➡️ Processing ID: {$house->id}");

                    try {
                        $payload = [
                            "tracking_id" => $house->id, // or $house->tracking_id if exists
                            "map_id"      => $house->map_id, // assuming dss = map_id
                            "account_no"  => $house->account_no,
                        ];

                        $response = Http::withToken('LIVEKEY_0XJLDYJZOQWF8UQ9XWVTH')
                            ->timeout(60)
                            ->post('https://msms.ibedc.com/api/v2/setup/update', $payload);

                        $this->info("Status: " . $response->status());

                        Log::info('MSMS Request', [
                            'payload' => $payload,
                            'response' => $response->json()
                        ]);

                        if ($response->successful()) {

                            // ✅ Update map_id so it won't be picked again
                            $house->update([
                                'service_class' => "msms_updated",
                            ]);

                            $this->info("✅ Successfully updated ID: {$house->id} with MAP ID: {$house->map_id} and Account No: {$house->account_no}");

                        } else {

                            $this->error("❌ Failed ID: {$house->id}");
                        }

                    } catch (\Exception $e) {

                        Log::error('MSMS Error', [
                            'id' => $house->id,
                            'message' => $e->getMessage()
                        ]);

                        $this->error("❌ Exception for ID {$house->id}: " . $e->getMessage());
                    }
                }
            });

        $this->info("✅ MSMS Update Completed.");
        
    }
}

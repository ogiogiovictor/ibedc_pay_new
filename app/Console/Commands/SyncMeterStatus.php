<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NAC\UploadHouses;
use Illuminate\Support\Facades\Http;

class SyncMeterStatus extends Command
{
    protected $signature = 'sync:meter-status';
    protected $description = 'Sync meter status from IBEDC API';

    public function handle()
    {
        $page = 1;
        $hasMore = true;

        while ($hasMore) {
            $this->info("➡️ Fetching Page: {$page}");

            $response = Http::withHeaders([
                'Authorization' => 'Bearer LIVEKEY_0XJLDYJZOQWF8UQ9XWVTH',
                'Accept' => 'application/json',
            ])->get("https://msms.ibedc.com/api/v2/setup/getstatus", [
                'pageid' => $page
            ]);

            if (!$response->successful()) {
                $this->error("❌ Failed on page {$page}");
                break;
            }

            $body = $response->json();

            if (empty($body['data'])) {
                $this->info("✅ No more records. Stopping...");
                break;
            }

            foreach ($body['data'] as $record) {

                $trackingId = $record['TrackingID'];

                $this->info("➡️ Processing TrackingID: {$trackingId}");

                $house = UploadHouses::where('id', $trackingId)->where("paid_for_meter", "No")
                ->whereNull('service_class')->first();
                //  $house = UploadHouses::whereIN('id', ['131809', '116772', '127041', '127634', '124539', '99275', '123202', '123203', '123204', '127143', '128399', '119764', '119469', '122474', '122818', '120807', '120808', '120806', '120805', '100695', '127318', '98949', '118918', '121577', '127839', '125699', '118869', '114199', '133867', '１３８６７３', '１３８６７２', '１３８６７１', '１４５９５４', '１４５９５３', '１４５６０５', '１４０１０３', '１３８６６０', '１３８６５９', '１３８６５７', '１３８６５６', '１３８６５５', '１４５６１８', '１４７１１２', '１４７１１ｉ ', '１４１１８８', '１５２７５１',
                //  ->first();

               // $this->line(json_encode($house ?? [], JSON_PRETTY_PRINT));

                if (!$house) {
                    $this->warn("⚠️ No record found for TrackingID: {$trackingId}");
                    continue;
                }

                try {

               $this->info("✅ Meter Allocation: {$record['MeterAllocated']}, Meter Installation: {$record['MeterInstalled']} for TrackingID: {$trackingId}");

                $allocated = strtoupper(trim($record['MeterAllocated']));
                $installed = strtoupper(trim($record['MeterInstalled']));

                if ($allocated === 'YES' && $installed === 'YES') {

                    $updated = $house->update([
                        'map_id' => $record['MAPID'] ?? null,
                        'paid_for_meter' => strtoupper(trim($record['PaymentMade'])) === 'YES' ? 'Yes' : 'No',
                    ]);

                    if ($updated) {
                        $this->info("✅ Updated TrackingID: {$trackingId}");
                    } else {
                        $this->warn("⚠️ No DB change for TrackingID: {$trackingId}");
                    }

                } else {
                    $this->info("⏭ Skipped TrackingID: {$trackingId}");
                }


                } catch (\Exception $e) {
                    $this->error("❌ Error updating {$trackingId}: " . $e->getMessage());
                }
            }

            $page++;
        }

        $this->info("🎉 Sync completed!");
    }

    /**
     * Clean amount like N117,175.00 → 117175.00
     */
    private function cleanAmount($amount)
    {
        if (!$amount) return null;

        return (float) str_replace(['N', ','], '', $amount);
    }

    /**
     * Convert API date to proper format
     */
    private function formatDate($date)
    {
        if (!$date || $date === 'N/A') {
            return null;
        }

        try {
            return Carbon::parse($date)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }
}
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NAC\UploadHouses;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class UbvsResyncAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:ubvs-resync-account  {tracking_ids}';
    protected $description = 'Process MSMS map generation using tracking IDs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
       
       $this->info('🔄 Starting processing using provided tracking IDs...');
        $this->newLine();

        $trackingIdsInput = $this->argument('tracking_ids');

        // Convert "1,2,3" → [1,2,3]
        $trackingIds = array_filter(array_map('trim', explode(',', $trackingIdsInput)));

        if (empty($trackingIds)) {
            $this->error('❌ No tracking IDs provided.');
            return;
        }

        $totalProcessed = 0;
        $totalSuccess = 0;
        $totalFailed = 0;

        UploadHouses::with(['landlordinfo', 'account'])
            ->whereIn('id', $trackingIds)
            //->where('status', ['2', '1'])
            ->whereIn('paid_for_meter', ["No", "Old"])
            ->whereNull('map_id')
            ->chunk(50, function ($records) use (&$totalProcessed, &$totalSuccess, &$totalFailed) {

                foreach ($records as $data) {

                    try {
                        $this->info("➡️ Processing ID: {$data->id}");

                        $response = $this->synctoMSMS($data->id);

                        $this->line("Status Code: " . ($response['status'] ?? 'N/A'));
                        $this->line("Message: " . ($response['message'] ?? 'N/A'));

                        $this->line("Payload:");
                        $this->line(json_encode($response['payload'] ?? [], JSON_PRETTY_PRINT));

                        $this->line("Response:");
                        $this->line(json_encode($response['response'] ?? [], JSON_PRETTY_PRINT));

                        if ($response['success']) {
                            $totalSuccess++;
                            $this->info("✅ Success for ID: {$data->id}");
                        } else {
                            $totalFailed++;
                            $this->error("❌ Failed for ID: {$data->id}");
                        }

                    } catch (\Exception $e) {

                        $totalFailed++;

                        Log::error('MSMS Command Error', [
                            'id' => $data->id,
                            'error' => $e->getMessage()
                        ]);

                        $this->error("❌ Exception for ID: {$data->id}");
                    }

                    $this->newLine();
                    $totalProcessed++;
                }
            });

        $this->info('-----------------------------------');
        $this->info("✅ Processing completed.");
        $this->info("Total Processed: {$totalProcessed}");
        $this->info("Successful: {$totalSuccess}");
        $this->info("Failed: {$totalFailed}");
    }

    /**
     * 🔥 MAIN SYNC FUNCTION
     */
    private function synctoMSMS($id)
    {
        try {
            $apiKey = "LIVEKEY_0XJLDYJZOQWF8UQ9XWVTH" ?? env('MSMS_API_KEY');

            $data = UploadHouses::with(['landlordinfo', 'account'])
                ->findOrFail($id);

            /**
             * 🔹 FETCH LOCATIONS
             */
            $locationResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Accept' => 'application/json'
            ])->get('https://msms.ibedc.com/api/v2/setup/locations');

            if (!$locationResponse->successful()) {
                return [
                    "success" => false,
                    "status" => $locationResponse->status(),
                    "payload" => null,
                    "response" => $locationResponse->json(),
                    "message" => "Failed to fetch locations"
                ];
            }

            $locations = $locationResponse->json();

            if (!isset($locations['data'])) {
                return [
                    "success" => false,
                    "status" => null,
                    "payload" => null,
                    "response" => $locations,
                    "message" => "Invalid location response structure"
                ];
            }

            $regions = $locations['data']['Regions'];
            $bhubs = $locations['data']['BusinessHubs'];

            /**
             * 🔥 MAPPING
             */
            $bhubMapping = [
                'moweibafo' => 'Mowe-Ibafo',
                'ilesa' => 'Ilesha',
                'ileife' => 'Ileife',
            ];

            $normalize = fn($v) => strtolower(str_replace(['-', ' '], '', $v));

            $normalizedInput = $normalize($data->business_hub);
            $businessHubName = $bhubMapping[$normalizedInput] ?? $data->business_hub;

            /**
             * 🔹 REGION
             */
            $region = collect($regions)->firstWhere('name', $data->region . ' Region');
            $region_id = $region['id'] ?? null;

            /**
             * 🔹 BUSINESS HUB
             */
            $bhub = collect($bhubs)->firstWhere('name', $businessHubName);

            if (!$bhub) {
                $bhub = collect($bhubs)->first(function ($item) use ($normalizedInput, $normalize) {
                    return $normalize($item['name']) === $normalizedInput;
                });
            }

            $bhub_id = $bhub['id'] ?? null;

            if (!$bhub_id) {
                return [
                    "success" => false,
                    "status" => null,
                    "payload" => null,
                    "response" => null,
                    "message" => "BHUB mapping failed for: {$data->business_hub}"
                ];
            }

            /**
             * 🔹 PAYLOAD
             */
            $payload = [
                "tracking_id" => $data->id,
                "cust_name" => trim(($data->landlordinfo->landlord_surname ?? '') . ' ' . ($data->landlordinfo->landlord_othernames ?? '')),
                "address" => $data->full_address ?? '',
                "lga" => $data->lga ?? '',
                "city" => $businessHubName,
                "state" => $data->state ?? '',
                "premises_use" => $data->use_of_premise ?? '',
                "phone_no" => $data->landlordinfo->landlord_telephone ?? $data->account->phone ?? '',
                "email" => $data->landlordinfo->landlord_email ?? $data->account->email ?? '',
                "region_id" => $region_id,
                "bhub_id" => $bhub_id,
                "service_center" => $data->service_center ?? '',
                "dss_id" => $data->dss ?? '',
                "id_type" => $data->landlordinfo->landlord_personal_identification ?? '',
                "id_no" => $data->landlordinfo->nin_number ?? '',
                "contact_name" => trim(($data->account->surname ?? '') . ' ' . ($data->account->firstname ?? '') . ' ' . ($data->account->other_name ?? '')),
                "contact_phone" => $data->account->phone ?? $data->landlordinfo->landlord_telephone ?? '',
                "contact_email" => $data->account->email ?? $data->landlordinfo->landlord_email ?? ''
            ];

            /**
             * 🔹 SEND REQUEST
             */
            $createResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Accept' => 'application/json'
            ])->post('https://msms.ibedc.com/api/v2/setup/create', $payload);

            $responseData = $createResponse->json();
            $mapId = $responseData['data']['MAP ID'] ?? null;

            $customerEmail = $data->account->email;
            $customerName = $data->account->surname . ' ' . $data->account->firstname;
            $landlordEmail = $data->landlordinfo->landlord_email;

            if ($createResponse->successful() && $mapId) {

                UploadHouses::where('id', $id)->update([
                    'map_id' => $mapId
                ]);

                Mail::raw("Dear {$customerName},\n\nYour meter processing with tracking ID {$data->id} has been successfully validated.\n\nYour MAP ID is: {$mapId}\n\nPlease proceed to the MSMS portal to complete your payment.\n\nhttps://msms.ibedc.com/\n\nThank you.", function ($message) use ($customerEmail, $landlordEmail) {
                    $message->to($customerEmail)
                            ->cc($landlordEmail)
                            ->subject('Meter Process Validated');
                });

                return [
                    "success" => true,
                    "status" => $createResponse->status(),
                    "payload" => $payload,
                    "response" => $responseData,
                    "message" => "Success"
                ];
            }

            return [
                "success" => false,
                "status" => $createResponse->status(),
                "payload" => $payload,
                "response" => $responseData,
                "message" => $responseData['message'] ?? 'API failed'
            ];

        } catch (\Exception $e) {

            return [
                "success" => false,
                "status" => null,
                "payload" => null,
                "response" => null,
                "message" => $e->getMessage()
            ];
        }
    }
}

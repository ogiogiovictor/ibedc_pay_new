<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NAC\UploadHouses;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class Generatemapid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generatemapid {year} {month}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate MSMS MAP IDs for all eligible records created in the given year and month';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $year = $this->argument('year');
        $month = $this->argument('month');

        if (!ctype_digit((string) $year) || !ctype_digit((string) $month) || $month < 1 || $month > 12) {
            $this->error('❌ Invalid year or month provided.');
            return;
        }

        $this->info("🔄 Starting processing for {$year}-{$month}...");
        $this->newLine();

        $totalProcessed = 0;
        $totalSuccess = 0;
        $totalFailed = 0;

        UploadHouses::with(['landlordinfo', 'account'])
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->whereIn('paid_for_meter', ["No", "Old"])
            ->whereNull('map_id')
            ->whereNotNull('dss')
            ->where('status', '2')
            ->chunk(50, function ($records) use (&$totalProcessed, &$totalSuccess, &$totalFailed) {

                foreach ($records as $data) {

                    $response = [];

                    try {
                        $this->info("➡️ Processing ID: {$data->id}");

                        $response = $this->synctoMSMS($data->id);

                        $this->line("Status Code: " . ($response['status'] ?? 'N/A'));
                        $this->line("Message: " . ($response['message'] ?? 'N/A'));

                        if (!empty($response['error'])) {
                            $this->error("Error: " . $response['error']);
                        }

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

                            Log::error('MSMS Command Failed', [
                                'id' => $data->id,
                                'status' => $response['status'] ?? null,
                                'message' => $response['message'] ?? null,
                                'error' => $response['error'] ?? null,
                                'payload' => $response['payload'] ?? null,
                                'response' => $response['response'] ?? null,
                            ]);
                        }

                    } catch (\Exception $e) {

                        $totalFailed++;

                        $this->error("❌ Exception for ID: {$data->id}");
                        $this->line($e->getMessage());

                        $this->line("Payload:");
                        $this->line(json_encode($response['payload'] ?? [], JSON_PRETTY_PRINT));

                        $this->line("Response:");
                        $this->line(json_encode($response['response'] ?? [], JSON_PRETTY_PRINT));

                        Log::error('MSMS Command Exception', [
                            'id' => $data->id,
                            'error' => $e->getMessage(),
                            'payload' => $response['payload'] ?? null,
                            'response' => $response['response'] ?? null,
                        ]);
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
        $payload = null;
        $responseData = null;

        try {
            $apiKey = env('MSMS_API_KEY', 'LIVEKEY_0XJLDYJZOQWF8UQ9XWVTH');

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
                    "message" => "BHUB mapping failed for: " . json_encode($data->business_hub)
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

            if ($createResponse->successful() && $mapId) {

                UploadHouses::where('id', $id)->update([
                    'map_id' => $mapId
                ]);

                $customerEmail = $data->account->email ?? null;
                $customerName = trim(($data->account->surname ?? '') . ' ' . ($data->account->firstname ?? ''));
                $landlordEmail = $data->landlordinfo->landlord_email ?? null;

                if ($customerEmail) {
                    Mail::raw("Dear {$customerName},\n\nYour meter processing with tracking ID {$data->id} has been successfully validated.\n\nYour MAP ID is: {$mapId}\n\nPlease proceed to the MSMS portal to complete your payment.\n\nhttps://msms.ibedc.com/\n\nThank you.", function ($message) use ($customerEmail, $landlordEmail) {
                        $message->to($customerEmail);

                        if ($landlordEmail) {
                            $message->cc($landlordEmail);
                        }

                        $message->subject('Meter Process Validated');
                    });
                }

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
                "payload" => $payload,
                "response" => $responseData,
                "message" => "An exception occurred while syncing the account",
                "error" => $e->getMessage()
            ];
        }
    }
}

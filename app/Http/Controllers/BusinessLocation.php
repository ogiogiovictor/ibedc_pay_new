<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use App\Models\NAC\UploadHouses;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class BusinessLocation extends BaseAPIController
{
    public function getRegion() {

       $regions = Location::select("region")->distinct()->pluck("region");
       return response()->json($regions);

    }

    public function getBusinessHubs($region) {

         $business_hubs = Location::select("bhub", "region")->distinct()->where("region", $region)->get()->toArray();
         return response()->json($business_hubs);

    }

    public function getServiceCenter($businesshub) {

        $service_center = Location::select("service_center", "bhub")->where(["bhub" =>$businesshub] )->get()->toArray();
         return response()->json($service_center);

    }

    public function changeProfile(Request $request) {

       $validated = $request->validate([
            'email' => 'required|email',
            'region' => 'sometimes|string|nullable',
            'business_hub' => 'sometimes|string|nullable',
            'sc' => 'sometimes|string|nullable',
            'authority' => 'sometimes|string|nullable',
        ]);

          $user = User::where("email", $validated['email'])->first();

        if (!$user) {
            return $this->sendError("User not found", Response::HTTP_NOT_FOUND);
        }

         // Only update fields that are present in the request
        $updateData = collect($validated)->only(['region', 'business_hub', 'sc', 'authority'])->filter()->toArray();

        if (!empty($updateData)) {
            $user->update($updateData);

             return $this->sendSuccess($user, "Profile Successfully Updated", Response::HTTP_OK);
        }

         return $this->sendError("Error Updating Request", Response::HTTP_NOT_FOUND);


    }


    public function meterProgrammingDecisionTree(Request $request) {

        
            $validated = $request->validate([
                'map_id' => 'required|string',
            ]);

            try {

                $response = Http::timeout(60)
                    ->acceptJson()
                    ->post('https://msms.ibedc.com/api/v2/setup/fetchdetails', [
                        'map_id' => $validated['map_id'],
                    ]);

                if (!$response->successful()) {

                    return response()->json([
                        'status'  => false,
                        'message' => 'Unable to fetch customer details from MSMS',
                        'error'   => $response->body(),
                    ], $response->status());
                }

                $result = $response->json();

                if (!$result['status']) {
                    return response()->json([
                        'status'  => false,
                        'message' => $result['message'] ?? 'Request failed',
                    ], 400);
                }

                $data = $result['data'];

                return response()->json([
                    'status'  => true,
                    'message' => $result['message'],
                    'data'    => [
                        'customer_name' => $data['CustomerName'] ?? null,
                        'tracking_id'   => $data['TrackingID'] ?? null,
                        'account_no'    => $data['AccountNo'] ?? null,
                        'map_id'        => $data['MAPID'] ?? null,
                        'address'       => $data['Address'] ?? null,
                        'created_at'    => $data['CreatedAt'] ?? null,

                        'evaluation' => [
                            'status'     => $data['EvaluationInfo']['TE_Status']
                                ?? $data['EvaluationInfo']['TEStatus']
                                ?? 'Pending',

                            'meter_type' => $data['EvaluationInfo']['MeterType'] ?? null,
                            'date'        => $data['EvaluationInfo']['TE_Date'] ?? null,
                        ],

                        'payment' => [
                            'status'        => $data['PaymentInformation']['PaymentStatus'] ?? 'Pending',
                            'amount_paid'   => $data['PaymentInformation']['AmountPaid'] ?? null,
                            'date_paid'     => $data['PaymentInformation']['DatePaid'] ?? null,
                            'trx_reference' => $data['PaymentInformation']['TRXReference'] ?? null,
                            'map_vendor'    => $data['PaymentInformation']['MAPVendor'] ?? null,
                            'meter_type'    => $data['PaymentInformation']['MeterType'] ?? null,
                        ],

                        'allocation' => [
                            'status'          => $data['AllocationInformation']['AllocationStatus'] ?? 'Pending',
                            'meter_no'        => $data['AllocationInformation']['MeterNo'] ?? null,
                            'allocation_date' => $data['AllocationInformation']['AllocationDate'] ?? null,
                        ],

                        'installation' => [
                            'status'       => $data['InstallationInformation']['InstallationStatus'] ?? 'Pending',
                            'meter_no'     => $data['InstallationInformation']['MeterNo'] ?? null,
                            'date'         => $data['InstallationInformation']['InstallationDate'] ?? null,
                            'installed_by' => $data['InstallationInformation']['InstalledBy'] ?? null,
                            'verified'     => $data['InstallationInformation']['InstallationVerified'] ?? null,
                            'certified'    => $data['InstallationInformation']['InstallationCertified'] ?? null,
                        ],
                    ]
                ]);

            } catch (\Exception $e) {

                return response()->json([
                    'status'  => false,
                    'message' => 'An error occurred while connecting to MSMS',
                    'error'   => $e->getMessage(),
                ], 500);

            
            }

    }


    public function programMeter(Request $request) {

     //first validate map_id must not be empty
        $validated = $request->validate([
            'map_id' => 'required|string',
        ]);

     //Second when the map_id is valid check if exist in UploadHouses using map_id to compare
        $house = UploadHouses::where('map_id', $validated['map_id'])->first();

     // if not exist return error message "MAP ID not found or not allocated yet"
        if (!$house) {
            return $this->sendError("MAP ID not found or not allocated yet", Response::HTTP_NOT_FOUND);
        
        }

     // if exist and account number is allocation and programme is yes return "Meter already programmed"
      if ($house->account_no && $house->programme == 'Yes') {
            return $this->sendError("Meter already programmed", Response::HTTP_BAD_REQUEST);
        }

     // check map_id in msms if payment is made and meter is allocated and installed before programming the meter
        try {
    
                    $response = Http::withHeaders([
                            'Authorization' => 'Bearer LIVEKEY_0XJLDYJZOQWF8UQ9XWVTH',
                            'Accept' => 'application/json',
                        ])->timeout(60)
                        ->acceptJson()
                        ->post('https://msms.ibedc.com/api/v2/setup/fetchdetails', [
                            'map_id' => $validated['map_id'],
                        ]);
    
                    if (!$response->successful()) {
    
                        return response()->json([
                            'status'  => false,
                            'message' => 'Unable to fetch customer details from MSMS',
                            'error'   => $response->body(),
                        ], $response->status());
                    }
    
                    $result = $response->json();
    
                    if (!$result['status']) {
                        return response()->json([
                            'status'  => false,
                            'message' => $result['message'] ?? 'Request failed',
                        ], 400);
                    }
    
                    $data = $result['data'];
    
                    $paymentStatus = $data['PaymentInformation']['PaymentStatus'] ?? null;
                    $allocationStatus = $data['AllocationInformation']['AllocationStatus'] ?? null;
                    $installationStatus = $data['InstallationInformation']['InstallationStatus'] ?? null;
    
                    if ($paymentStatus === 'Completed' && $allocationStatus === 'Meter Allocated' && $installationStatus === 'Meter Installed') {
                        
                        // Here you would add the logic to program the meter using the MAP ID
                        // For example, you might send a request to another endpoint to trigger the programming
    
                        return response()->json([
                            'data' => $data,
                            'status'  => true,
                            'message' => 'Meter programming initiated successfully',
                        ]);
    
                    } else {
                        return response()->json([
                            'status'  => false,
                            'message' => 'Meter cannot be programmed. Ensure payment is made, meter is allocated and installed.',
                        ], 400);
                    }
    
                } catch (\Exception $e) {
    
                    return response()->json([
                        'status'  => false,
                        'message' => 'An error occurred while connecting to MSMS',
                        'error'   => $e->getMessage(),
                    ], 500);
    
                
                }

     //send mapid to msms to program the meter and return the response from msms to the client

    }

}

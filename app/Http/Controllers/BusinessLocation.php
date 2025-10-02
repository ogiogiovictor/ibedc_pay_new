<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

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

}

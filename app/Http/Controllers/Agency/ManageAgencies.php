<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use App\Helpers\StringHelper;
use App\Models\Agency\Agents;
use App\Models\Agency\AgencyBH;
use App\Enums\RoleEnum;
use App\Models\ECMI\BusinessUnit;
use App\Http\Requests\AgencyRequest;
use App\Models\Agency\Agencies as DAgency;
use Illuminate\Support\Str;

class ManageAgencies extends BaseAPIController
{
     /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // $this->middleware('can:'.RoleEnum::super_admin()->value);
        // $newRequest = $request->merge(['agent_code' => StringHelper::generateUUIDReference()]);
        // $createAgency = Agents::create($newRequest->all());
       // Validate the request
        $validated = $request->validate([
            'agent_name' => 'required|string|max:255',
            'agent_email' => 'required|email|max:255|unique:agencies,agent_email',
            'agent_official_phone' => 'required|string|max:20',
            'no_of_agents' => 'required|integer|min:1',
            'status' => 'required|boolean',
        ]);

        // Create agency
        $createAgency = DAgency::create([
            'agent_code' => Str::random(5), // e.g., "A1B2C"
            'agent_name' => $validated['agent_name'],
            'agent_email' => $validated['agent_email'],
            'agent_official_phone' => $validated['agent_official_phone'],
            'no_of_agents' => $validated['no_of_agents'],
            'status' => $validated['status'],
        ]);

        return $this->sendSuccess([
            'payload' => $createAgency,
            'message' => 'Agency Successfully Created',
        ], 'Successful', Response::HTTP_OK);

    }


    public function create(Request $request) {

       $businesshub =  AgencyBH::create([
            'agency_id' => $request->agency,
            'business_hub' => $request->business_hub
        ]);

         return $this->sendSuccess([
            'payload' => $businesshub,
            'message' => 'Business Hub Successufully Created',
        ], 'Successful', Response::HTTP_OK);


    }


    public function getHubs(Request $request) {

        $getHubs = BusinessUnit::select("BUID", "State", "bucode")->get();
          return $this->sendSuccess([
            'payload' => $getHubs,
            'message' => 'Business Hub Successufully Created',
        ], 'Successful', Response::HTTP_OK);
    }


}

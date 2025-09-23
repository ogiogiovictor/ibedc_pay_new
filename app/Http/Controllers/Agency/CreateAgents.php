<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\AgencyRequest;
use App\Models\Agency\Agents;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use App\Helpers\StringHelper;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Wallet\WalletUser;
use App\Enums\RoleEnum;
use App\Models\Transactions\PaymentTransactions;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Hash;
use App\Models\Agency\Agencies as DAgency;




class CreateAgents extends BaseAPIController
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(RegisterRequest $request)
    {
         // Check if the user has one of the allowed roles
        if (!auth()->user()->hasAnyRole([RoleEnum::super_admin()->value, RoleEnum::agency_admin()->value])) {
            return $this->sendError('Unauthorized.', Response::HTTP_FORBIDDEN);
        }

        try {

            // Create the user
            //$user = User::create($request->all());
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'authority' => $request->authority,
                'agency' => $request->agency,
                'status' => 1,
                'pin' => 0,
                'user_code' => DAgency::where("id", $request->agency)->value("agent_code"). rand(4)
            ]);

            // Assign role if authority exists
            if (isset($request->authority)) {
                $user->assignRole(strtolower($request->authority));
            }

            // Dispatch welcome email
           // dispatch(new RegistrationJob($user));

            return $this->sendSuccess([
                'payload' => $user,
                'message' => 'A PIN has been generated for your account. Please check your email for the PIN to complete the registration process.',
            ], 'PIN generated', Response::HTTP_OK);

        } catch (\Exception $e) {
        
            return $this->sendError('Error occurred: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}

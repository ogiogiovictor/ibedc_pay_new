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


class AgencyController extends BaseAPIController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
      
       $account_number = $request->query('account_number');
       $MeterNo = $request->query('MeterNo');
       $agency = $request->query('agency');

        $user = Auth::user();

      
        $query = PaymentTransactions::query();

        // Add filters based on query parameters
        if ($account_number) {
            $query->where('account_number', $account_number);
        }
        if ($MeterNo) {
            $query->where('MeterNo', $MeterNo);
        }
        if ($agency) {
            $query->where('agency', $agency);
        }

        // if ($agency !== null) {
        //     $query->where('agency', $agency);
        // }
    
        // Additional filtering based on user role
        if ($user->authority == RoleEnum::super_admin()->value || $user->authority == RoleEnum::admin()->value) {
            $getAgency = Agents::with('users')->get();
            // Order by creation date and paginate results
            $agencyPayments = $query->orderby("created_at", "desc")->paginate(30);
        } elseif ($user->authority == RoleEnum::agency_admin()->value) {
            $getAgency = Agents::where('id', $user->agency)->with('users')->get();
            // Filter transactions by the user's agency and paginate results
            $agencyPayments = $query->where('agency', $user->agency)->orderby("created_at", "desc")->paginate(30);
        }

        return $this->sendSuccess([
            'payload' => [
                'agency' => $getAgency,
                'payments' => $agencyPayments
            ],
            'message' => 'Agency Successfully Loaded',
        ], Response::HTTP_OK);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
       
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AgencyRequest $request)
    {
       $this->middleware('can:'.RoleEnum::super_admin()->value);
        $newRequest = $request->merge(['agent_code' => StringHelper::generateUUIDReference()]);
        $createAgency = Agents::create($newRequest->all());

        return $this->sendSuccess( [
            'payload' => $createAgency,
            'message' => 'Agency Successfully Created',
        ], 'Successful', Response::HTTP_OK);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $showAgency = Agents::where("id", $id);

        return $this->sendSuccess([
            'payload' => $showAgency,
            'message' => 'Agency Successfully Loaded',
        ], 'Successful', Response::HTTP_OK);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function authenticate(LoginRequest $request){

        if (!isset($request->authority) &&  $request->authority != "agent") {
            return $this->sendError('User do not have access to this app', 'ERROR', Response::HTTP_UNAUTHORIZED);
        }
    

        //$user_status = User::where(["email" => $request->email, "authority" => "agent"])->first();

        $user_status = User::where('email', $request->email)->where(function($query) {
            $query->where('authority', 'agent')->orWhere('authority', 'agency_admin');
        })->first();

        if(!$user_status || $user_status->status != 1){
            return $this->sendError('User Not Activated Or User Does Not Exists' , 'ERROR', Response::HTTP_UNAUTHORIZED);
        }

        if($request->expectsJson()) {

            // Validate the login request
        $validatedData = $request->validated();

        // Attempt to log in the user
        if (Auth::attempt($validatedData)) {
            // Authentication passed...
            $user = Auth::user();

           
            // You can customize the response based on your needs
            return $this->sendSuccess([
                'user' => $user,
                'token' => $user->createToken('Authorization')->plainTextToken,
                'agency' => $user->agency ? Agents::where('id', $user->agency)->first() : 0, // Include the agency agent_name
                'wallet' => $user->wallet,
              //  'account' => $user->virtualAccount,
            ], 'LOGIN SUCCESSFUL', Response::HTTP_OK);
        }

        // Authentication failed
        return $this->sendError('Invalid credentials', 'ERROR', Response::HTTP_UNAUTHORIZED);

        }else {
            return $this->sendError("Error", "Error Loading Data, Something went wrong(NOT JSON())", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

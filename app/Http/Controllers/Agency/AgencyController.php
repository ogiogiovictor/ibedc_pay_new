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



class AgencyController extends BaseAPIController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $getAgency = Agents::all();

        return $this->sendSuccess([
            'payload' => $getAgency,
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

        if (!isset($request->authority) || $request->authority != "agent") {
            return $this->sendError('User do not have access to this app', 'ERROR', Response::HTTP_UNAUTHORIZED);
        }
    

        $user_status = User::where(["email" => $request->email, "authority" => "agent"])->first();

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
               // 'wallet' => $user->wallet,
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

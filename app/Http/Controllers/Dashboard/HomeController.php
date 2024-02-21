<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Interfaces\HomeRepositoryInterface;
use App\Interfaces\TransactionRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use Illuminate\Support\Facades\Auth;
use App\Services\PinService;

class HomeController extends BaseAPIController
{
    private HomeRepositoryInterface $profile;
    private TransactionRepositoryInterface $mytrans;


    public function __construct(HomeRepositoryInterface $profile, TransactionRepositoryInterface $mytrans) {

        $this->profile = $profile;
        $this->mytrans = $mytrans;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        return $this->sendSuccess([

           'user' => $this->profile->index($user->id),
        ], 'PROFILE LOADED', Response::HTTP_OK);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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


    public function outBalance(Request $request){

        $validatedData = $request->validate([
            'email' => 'required',
            "account_no" => 'required',
            "account_type" => 'required'
        ]);

        $sendPin = (new PinService)->processPin(Auth::user()->email);
           // $user = $this->profile->userprofile(Auth::user()->id);

            return $this->sendSuccess([
                'user' => $validatedData,
             ], 'PROFILE LOADED', Response::HTTP_OK);
             

        //Find transaction
        // $trans =  $this->mytrans->checkifexist(Auth::user()->id, $request->account_no);

        // if($trans){

        //     $sendPin = (new PinService)->processPin(Auth::user()->email);
        //    // $user = $this->profile->userprofile(Auth::user()->id);

        //     return $this->sendSuccess([
        //         'user' => $validatedData,
        //      ], 'PROFILE LOADED', Response::HTTP_OK);
             
        // } else {
        //     // Incorrect PIN
        //     return $this->sendError('That Account No Is Not Mapped To Your Profile', 'ERROR', Response::HTTP_UNAUTHORIZED);
        // }

    }

    public function showBalance(Request $request) {

        $validatedData = $request->validate([
            'email' => 'required',
            "account_no" => 'required',
            "pin" => "required",
            "account_type" => 'required'
        ]);

        $checkPin = $this->profile->checkPin($validatedData['email'], $validatedData['pin']);

        if($checkPin){

            if($validatedData['account_type'] == 'Prepaid'){

                $addBalance = 0;
                $subAccountBal = $this->profile->getSubAccount($validatedData['account_no']);

                if($subAccountBal){
                    $subAccountBalFpUnit = $this->profile->getSubAccountFPUnit($validatedData['account_no']);
                    $addBalance = $subAccountBal->Balance + $subAccountBalFpUnit;
                    $subAccountBal->Balance = number_format($addBalance, 2, ".", "");

                    return $this->sendSuccess([
                        'balance' => $subAccountBal,
                     ], 'BALANCE LOADED', Response::HTTP_OK);
                     
                } else {
                    return $this->sendSuccess([
                        'balance' => [],
                     ], 'NO BALANCE', Response::HTTP_OK);
    
                }

                
            } else {
                return $this->sendError('Please visit any of our office for your Postpaid Outstanding Balance', 'ERROR', Response::HTTP_UNAUTHORIZED);
            }

        } else {
            return $this->sendError('Invalid Pin for Retriving Outstanding Balance', 'ERROR', Response::HTTP_UNAUTHORIZED);
        }




    }







}

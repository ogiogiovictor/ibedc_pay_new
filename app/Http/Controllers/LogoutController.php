<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use Illuminate\Support\Facades\Auth;


class LogoutController extends BaseAPIController
{
    public function Logout(Request $request){
        if(!$request->email){
            return $this->sendError('Requested Parameters Missing', 'ERROR', Response::HTTP_UNAUTHORIZED);
        }

        if(!Auth::check()){
            return $this->sendError('No Data', 'ERROR', Response::HTTP_UNAUTHORIZED);
        }

        $user = auth()->user()->tokens()->delete();

         // Return the user object, token, and authorization
         return $this->sendSuccess([
            'user' => $user,
        ], 'User Successfully Logged Out', Response::HTTP_OK);
    }
}

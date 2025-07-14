<?php

//declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use App\Models\Setting\ApplicationSetting;
use Illuminate\Support\Facades\Http;

class NinService  extends BaseAPIController
{
    public function authenticate()
    {
        //https://fontanella.app/api/v1/login
            try {
           $response = Http::withHeaders([
                'X-API-KEY' => env("NIN_KEY"), // 'zMRaxKw9YTiTl5HD5sf9My0wg3s2vV2HeYip5wg0',
                'X-USER-ID' => env("NIN_ID"),
                'Accept' => 'application/json',
            ])->post(env("NIN_URL") . "login", [  
                'email' => env("NIN_EMAIL"),
                'password' => env("NIN_PASS"),
            ]);

            return $response->json(); // Always return JSON-decoded array

            // Return raw response body and status code directly
            // return response($response->body(), $response->status())
            //     ->header('Content-Type', $response->header('Content-Type'));

        } catch (\Exception $e) {
            // If the API call fails (e.g., timeout, DNS failure), catch and return error
            return response()->json([
                'message' => 'NIN API request failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
          
    }
}

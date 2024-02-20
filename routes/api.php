<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Authenticate\RegisterController;
use App\Http\Controllers\Authenticate\LoginController;
use App\Http\Controllers\Dashboard\HomeController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Payment\CompletePayment;
use App\Http\Controllers\History\PaymentHistory;
use App\Http\Controllers\Help\ContactUsController;
use App\Http\Controllers\LogoutController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['prefix' => 'V2_ibedc_OAUTH_tokenReviwed', 'middleware' => 'myAuth'], function () {

    Route::resource('registration', RegisterController::class)->only(['index', 'store', 'show']);
    Route::post('verify-pin', [RegisterController::class, 'verifyPin']);
    Route::post('retry-verification-code', [RegisterController::class, 'retyCode']);
    Route::post('add-meter', [RegisterController::class, 'addMeter']);
    Route::post('authenticate', [LoginController::class, 'store']);
    

    Route::middleware('auth:sanctum')->group(function() {
    
        Route::post('logout', [LogoutController::class, 'Logout']);

        //////////////////////// HOME AND PROFILE INFORMATION /////////////////////////////
        Route::group(['prefix' => 'dashboard'], function () {  
            Route::resource('get-details', HomeController::class);
        });
 

        ///////////////////////// PAYMENT INITIATION FOR POSTPAID | PREPAID //////////////////
        Route::group(['prefix' => 'payment'], function () {  
            Route::resource('initiate-payment', PaymentController::class);
            Route::post('complete-payment', [CompletePayment::class, 'CompletePayment']);
        });

         ///////////////////////// PAYMENT INITIATION FOR POSTPAID | PREPAID //////////////////
         Route::group(['prefix' => 'history'], function () {  
            Route::get('get-history', [PaymentHistory::class, 'getHistory']);
        });

         ///////////////////////// OUTSTANDING BALANCE | PREPAID //////////////////
         Route::group(['prefix' => 'outstanding'], function () {  
            Route::post('get-balance', [HomeController::class, 'outBalance']);
            Route::post('show-balance', [HomeController::class, 'showBalance']);
        });

        ///////////////////////// OUTSTANDING BALANCE | PREPAID //////////////////
        Route::group(['prefix' => 'contact'], function () {  
            Route::post('help', [ContactUsController::class, 'store']);
        });


    });



});
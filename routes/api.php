<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Authenticate\RegisterController;
use App\Http\Controllers\Authenticate\LoginController;
use App\Http\Controllers\Authenticate\ForgotController;
use App\Http\Controllers\Dashboard\HomeController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Payment\CompletePayment;
use App\Http\Controllers\History\PaymentHistory;
use App\Http\Controllers\Help\ContactUsController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\Notification\PolarisPaymentNotification;
use App\Http\Controllers\Wallet\WalletController;
use App\Http\Controllers\Remove\DeleteController;
use App\Http\Controllers\Token\TokenController;
use App\Http\Controllers\VirtualAccount\VirtualController;
use App\Http\Controllers\Payment\WalletPaymentConfirmation;
use App\Http\Controllers\AppVersionController;


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

    Route::group(['prefix' => 'app'], function () {  
        Route::get('version', [AppVersionController::class, 'getVersionNumber']);
    });
    // Route::resource('registration', RegisterController::class)->only(['index', 'store', 'show']);
    // Route::post('verify-pin', [RegisterController::class, 'verifyPin']);
    // Route::post('retry-verification-code', [RegisterController::class, 'retyCode']);
    // Route::post('add-meter', [RegisterController::class, 'addMeter']);
    // Route::post('authenticate', [LoginController::class, 'store']);

    Route::controller(RegisterController::class)->group(function() {
        Route::resource('registration', RegisterController::class)->only(['index', 'store', 'show']);
        Route::post('verify-pin', 'verifyPin')->name('verify-pin');
        Route::post('retry-verification-code', 'retyCode')->name('retry-verification-code');
        Route::post('add-meter', 'addMeter')->name('add-meter');

        Route::post('start_registration', 'storeRegister')->name('startRegistration');
    });

    Route::post('authenticate', [LoginController::class, 'store']);
    Route::post('meter_authenticate', [LoginController::class, 'authLoginTest']);
   // Route::post('meter_authenticate_test', [LoginController::class, 'authLogin']);  //authLogin

    /////////////////////////// FORGOT PASSWORD IMPLEMENTATION ///////////////////////////////////
    // Route::post('forgot-password', [ForgotController::class, 'forgotPass']);
    // Route::post('verify-password', [ForgotController::class, 'verifyPass']);
    // Route::post('change-password', [ForgotController::class, 'changePass']);

    Route::controller(ForgotController::class)->group(function() {
        Route::post('forgot-password', 'forgotPass')->name('forgot-password');
        Route::post('verify-password', 'verifyPass')->name('verify-password');
        Route::post('change-password', 'changePass')->name('change-password');
    });
    
   

    Route::middleware('auth:sanctum')->group(function() {
    
        Route::post('logout', [LogoutController::class, 'Logout']);

        //////////////////////// HOME AND PROFILE INFORMATION /////////////////////////////
        Route::group(['prefix' => 'dashboard'], function () {  
            Route::resource('get-details', HomeController::class);
            Route::post('update-profile', [HomeController::class, 'profileUpdate']);
        });
 

        ///////////////////////// PAYMENT INITIATION FOR POSTPAID | PREPAID //////////////////
        Route::group(['prefix' => 'payment'], function () {  
            Route::resource('initiate-payment', PaymentController::class);
        
            Route::post('continue-payment', [PaymentController::class, 'continuePayment']);  // v2 process payment for flutterwave

           

            Route::controller(CompletePayment::class)->group(function() {
                Route::post('complete-payment', 'CompletePayment')->name('complete-payment');
                Route::get('get-token-notification', 'TokenNotifications')->name('get-token-notification');
                Route::post('retry-payment', 'retryPayment')->name('retry-payment');
            });

            Route::controller(WalletPaymentConfirmation::class)->group(function() {
                Route::post('wallet-payment', 'CompletePayment')->name('wallet-payment');
            });
        });

        Route::group(['prefix' => 'virtual'], function () {  
            Route::post('account', [VirtualController::class, 'createVirtualAccount']);
        });

         ///////////////////////// PAYMENT INITIATION FOR POSTPAID | PREPAID //////////////////
         Route::group(['prefix' => 'history'], function () {  
            Route::get('get-history', [PaymentHistory::class, 'getHistory']);
            Route::get('other-history', [PaymentHistory::class, 'getOtherHistory']);
            Route::get('get-bill-history', [PaymentHistory::class, 'getBillHistory']);
        });

         ///////////////////////// OUTSTANDING BALANCE | PREPAID //////////////////
        //  Route::group(['prefix' => 'outstanding'], function () {  
        //     Route::post('get-balance', [HomeController::class, 'outBalance']);
        //     Route::post('show-balance', [HomeController::class, 'showBalance']);
        // });

        Route::prefix('outstanding')->controller(HomeController::class)->group(function () {
            Route::post('get-balance', 'outBalance')->name('get-balance');
            Route::post('show-balance', 'showBalance')->name('show-balance');
        });

        ///////////////////////// OUTSTANDING BALANCE | PREPAID //////////////////
        Route::group(['prefix' => 'contact'], function () {  
            Route::post('help', [ContactUsController::class, 'store']);
        });


        ///////////////////////// OUTSTANDING BALANCE | PREPAID //////////////////
        // Route::group(['prefix' => 'wallet'], function () {  
        //     Route::get('wallet-balance-history', [WalletController::class, 'retrieve']);
        //     Route::get('wallet-summary', [WalletController::class, 'walletSummary']);
        // });

        Route::prefix('wallet')->controller(WalletController::class)->group(function () {
            Route::get('wallet-balance-history', 'retrieve')->name('wallet-balance-history');
            Route::get('wallet-summary', 'walletSummary')->name('wallet-summary');
        });

          ///////////////////////// DELETE ACCOUNT //////////////////
          Route::group(['prefix' => 'delete'], function () {  
            Route::resource('remove-account', DeleteController::class);
          });

          ///////////////////////////TOKEN NOTIFICATION //////////////////////
          Route::prefix('token')->controller(TokenController::class)->group(function () {
            Route::get('notification', 'GetNotification')->name('notification');
            Route::get('saved-meters', 'SavedMeters')->name('saved-meters');
          });

    });



});



Route::group(['prefix' => 'V2_polaris_OAUTHSIGNATURE_confirmation'], function () {
    Route::resource('notify_payment_account', PolarisPaymentNotification::class)->only(['index', 'store', 'show'])->middleware('verify.signature');
    //Route::resource('registration', RegisterController::class)->only(['index', 'store', 'show']);
});


//virtual account webhook
Route::post('/webhook/flutterwave', [VirtualController::class, 'handleFlutterwaveWebhookFCMB']);

Route::post('/webhook/dispatch/flutterwave', [VirtualController::class, 'handleSuccessWebHookDespatch']);

//Failed Transaction 
Route::post('/webhook/failed/flutterwave', [VirtualController::class, 'handleFailedWebhookFCMB']);


require_once __DIR__.'/agency.php';
require_once __DIR__.'/account.php';
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NAC\AccountController;
use App\Http\Controllers\NAC\NewAccountUpload;

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




Route::group(['prefix' => 'V4IBEDC_new_account_setup_sync', 'middleware' => 'myAuth'], function () {  

      
     Route::prefix('initiate')->controller(AccountController::class)->group(function () {
        Route::post('register-customer', 'store')->name('register-customer');
        Route::post('track-application', 'index')->name('track-application');

        Route::post('nin-validation', 'providedata')->name('nin-validation');

        Route::post('continue-application', 'continue')->name('continue-application');
        
        Route::post('final-application', 'final')->name('final-application');
        Route::post('upload-lecan-form-application', 'lecanapplication')->name('upload-lecan-form-application');
        Route::post('process_account', 'dtmprocess')->name('process_account');

        Route::post('change_account_location', 'changedtmprocess')->name('change_account_location');

        Route::middleware('auth:sanctum')->group(function() {
            Route::get('get_pending_account', 'getpendingaccounts')->name('process_account');
            Route::post('rejectform', 'reject')->name('rejectfrom');
            Route::post('approve_request', 'approveDTERequest')->name('approve_request');
        });
        
       // Route::post('upload-application', 'upload')->name('upload-application');
       // Route::post('complete-application', 'complete')->name('complete-application');

        ///////////////////// GET ALL REGIONS/BUSINESS HUBS/ SERVICE CENTER AND DSS. ///////////////////////////////////
        ///////////////////////This is the last point of the API /////////////////////////////////////////////////////
        Route::get('regions', 'region')->name('regions');
        Route::get('business_hub/{region_name}', 'businesshub')->name('business_hub');
        Route::get('all_business_hub', 'allBusinessHub')->name('all_business_hub');
        Route::get('get_dss', 'getDss')->name('get_dss');
        Route::get('service_centers/{business_hub_name}', 'getDssServiceCenter')->name('service_centers');
        Route::get('get_tarriff', 'getTarriff')->name('get_tarriff');
        Route::get('get_auth', 'NINService')->name('get_auth');
        

    });

    //Route::post('start_process', [AccountController::class, 'store']);


});


////////////////////////////////////////// LINK FOR DTM ///////////////////////////////////////////////////////
Route::group(['prefix' => 'V4IBEDC_new_account_setup_sync'], function () {  
    Route::prefix('initiate')->controller(NewAccountUpload::class)->group(function () {
        
        Route::post('validate_user', 'validateUser')->name('validate_user');

        Route::post('get_pending_account_upload', 'pendingUpload')->name('get_pending_account_upload');

    });
});



<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NAC\AccountController;

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
        Route::post('continue-application', 'continue')->name('continue-application');
        
        Route::post('upload-application', 'upload')->name('upload-application');


        
        Route::post('complete-application', 'complete')->name('complete-application');
        Route::post('final-application', 'final')->name('final-application');

        Route::post('upload-lecan-form-application', 'lecanapplication')->name('upload-lecan-form-application');

        


        ///////////////////// GET ALL REGIONS/BUSINESS HUBS/ SERVICE CENTER AND DSS. ///////////////////////////////////
        ///////////////////////This is the last point of the API /////////////////////////////////////////////////////
        Route::get('regions', 'region')->name('regions');
        Route::get('business_hub/{region_name}', 'businesshub')->name('business_hub');
        Route::get('get_dss/{region}/{business_hub_name}/{servicecenter}', 'getDss')->name('get_dss');
        Route::get('service_centers/{business_hub_name}', 'getDssServiceCenter')->name('service_centers');

    });

    //Route::post('start_process', [AccountController::class, 'store']);


});




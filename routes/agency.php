<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Authenticate\RegisterController;
use App\Http\Controllers\Agency\AgencyController;
use App\Http\Controllers\Agency\AgencySearchController;
use App\Http\Controllers\Agency\AgencyCollection;
use App\Http\Controllers\History\CustomerPaymentHistory;
use App\Http\Controllers\Billing\ArcGISValidation;
use App\Livewire\AgencyDashboard;
use App\Http\Controllers\History\CustomerBillHistory;

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

Route::group(['prefix' => 'V2_ibedc_OAUTH_agency_sync', 'middleware' => 'myAuth'], function () {

      
    Route::post('agent_authenticate', [AgencyController::class, 'authenticate']);

    Route::middleware('auth:sanctum')->group(function() {
        
    
        Route::resource('agency', AgencyController::class)->only(['index', 'store', 'show']); // only administrator
        Route::post('search', [AgencySearchController::class, 'searchCustomers']); // only administrator
        
        //Agency Collection | Target
        Route::get('collection_target', [AgencyCollection::class, 'agentCollection']);

        Route::prefix('customerhistory')->controller(CustomerPaymentHistory::class)->group(function () {
            Route::post('customer-history', 'customerHistory')->name('customer-history');
        });

        //Customer Pills
        Route::prefix('customerhistory')->controller(CustomerBillHistory::class)->group(function () {
            Route::post('bill-history', 'customerBills')->name('bill-history');
        });

        //POST BILLS


    });



});


Route::group(['prefix' => 'OXS2_gis_implementationX3', 'middleware' => 'myAuth'], function () {

    Route::prefix('custmomer_validation')->controller(ArcGISValidation::class)->group(function () {
        Route::post('get-customer', 'getCustomer')->name('get-customer');
    });

});


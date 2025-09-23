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


use App\Http\Controllers\Agency\CreateAgents;
use App\Http\Controllers\Agency\ManageAgencies;
use App\Http\Controllers\Agency\AgentProfile;

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
        
          // Only Super Administrator and Agency Admin Can Create Agents
        Route::resource('agency', AgencyController::class)->only(['index', 'store', 'show']); // only administrator

        // only administrator

        ///////////////////////////// CUSTOMERS ONLY ////////////////////////////////////////////
         //Customer Search
        Route::prefix('customerhistory')->controller(CustomerBillHistory::class)->group(function () {
            Route::post('bill-history', 'customerBills')->name('bill-history');
            Route::get('get-customers', 'getCustomers')->name('get-customers');
        });

         Route::post('search', [AgencySearchController::class, 'searchCustomers']);
         Route::get('getprofile', [AgentProfile::class, 'getProfile']); 
         Route::get('getagenthistory', [AgentProfile::class, 'getHistory']); 
         Route::get('getpaymentbyBH', [AgentProfile::class, 'getpaymentbyBH']); 

        Route::prefix('collection')->controller(AgencyCollection::class)->group(function () {

            Route::get('agentcollection', 'agentCollection')->name('agentcollection');
            Route::get('agencycollection', 'agencyCollection')->name('agencycollection');
            Route::get('commission', 'commission')->name('commission');
            Route::post('calculatecommission', 'commissioncalculation')->name('calculatecommission');
            Route::post('processsummary', 'commissionsummary')->name('processsummary');

        });


        //////////////////////////// ADMINISTRATIVE ROUTE ONLY /////////////////////////////////////////
         Route::post('register', [CreateAgents::class, 'create']); 
         Route::post('create_agency', [ManageAgencies::class, 'store']); 
         Route::post('add_agency_to_bh', [ManageAgencies::class, 'create']); 
         Route::get('get_business_hub', [ManageAgencies::class, 'getHubs']); 


       

       
        //POST BILLS


    });



});


Route::group(['prefix' => 'OXS2_gis_implementationX3', 'middleware' => 'myAuth'], function () {

    Route::prefix('custmomer_validation')->controller(ArcGISValidation::class)->group(function () {
        Route::post('get-customer', 'getCustomer')->name('get-customer');
    });

});


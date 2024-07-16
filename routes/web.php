<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Login;
use App\Livewire\Dashboard;
use App\Livewire\Transactions;
use App\Livewire\Users;
use App\Livewire\Wallets;
use App\Livewire\AppLog;
use App\Livewire\LogDetails;
use App\Livewire\LogTransactions;
use App\Livewire\CreateRole;
use App\Livewire\ViewTransaction;
use App\Livewire\AccessControl;
use App\Livewire\ViewContrologs;
use App\Livewire\AllAgencies;
use App\Livewire\AddAgencyTarget;
use App\Livewire\AgenctTransactions;
use App\Livewire\TransactionDetails;
use App\Livewire\Complaints;
use App\Livewire\AgencyDashboard;
use App\Livewire\ViewComplains;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/', Login::class);
Route::get('/login', Login::class)->name('login');


Route::middleware(['auth', 'check_access'])->group(function () {

    Route::middleware('auth:sanctum')->group(function() {

    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/transactions', Transactions::class)->name('transactions');
    Route::get('/transaction_details/{transaction_id}', TransactionDetails::class)->name('transaction_details');
    Route::get('/users', Users::class)->name('users');
    Route::get('/wallet_users', Wallets::class)->name('wallet_users');
    Route::get('/syslog', AppLog::class)->name('syslog');
    Route::get('/details/{id}', LogDetails::class)->name('details.show');  //Application logs
    Route::get('/log_transactions', LogTransactions::class)->name('log_transactions');
    Route::get('/roles', CreateRole::class)->name('roles');
    Route::get('/view_transactions/{transactions}', ViewTransaction::class)->name('view_transactions'); // view transaction v1
    Route::get('/assign_role', AccessControl::class)->name('assign_role');
    Route::get('/view_access_log/{id}', ViewContrologs::class)->name('view_access_log');
    Route::get('/agencies', AllAgencies::class)->name('agencies');
    Route::get('/add_target/{id}', AddAgencyTarget::class)->name('add_target');
    Route::get('/agency_transaction/{id}', AgenctTransactions::class)->name('agency_transaction');
    Route::get('/complaints', Complaints::class)->name('complaints');
    Route::get('/view_complaints/{id}', ViewComplains::class)->name('view_complaints');

    Route::middleware(['auth', 'agency_access'])->group(function () { 
        Route::get('/agency_dashboard', AgencyDashboard::class)->name('agency_dashboard');
    });
   

  });

});



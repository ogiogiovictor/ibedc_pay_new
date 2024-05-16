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

    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/transactions', Transactions::class)->name('transactions');
    Route::get('/users', Users::class)->name('users');
    Route::get('/wallet_users', Wallets::class)->name('wallet_users');
    Route::get('/syslog', AppLog::class)->name('syslog');
    Route::get('/details/{id}', LogDetails::class)->name('details.show');
    Route::get('/log_transactions', LogTransactions::class)->name('log_transactions');
    Route::get('/roles', CreateRole::class)->name('roles');
    Route::get('/view_transactions/{transactions}', ViewTransaction::class)->name('view_transactions');
    Route::get('/assign_role', AccessControl::class)->name('assign_role');
    Route::get('/view_access_log/{id}', ViewContrologs::class)->name('view_access_log');
    Route::get('/agencies', AllAgencies::class)->name('agencies');
    Route::get('/add_target/{id}', AddAgencyTarget::class)->name('add_target');
    Route::get('/agency_transaction/{id}', AgenctTransactions::class)->name('agency_transaction');
});
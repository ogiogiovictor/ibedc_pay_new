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


Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/transactions', Transactions::class)->name('transactions');
    Route::get('/users', Users::class)->name('users');
    Route::get('/wallet_users', Wallets::class)->name('wallet_users');
    Route::get('/syslog', AppLog::class)->name('syslog');
    Route::get('/details/{id}', LogDetails::class)->name('details.show');
    Route::get('/log_transactions', LogTransactions::class)->name('log_transactions');
    Route::get('/roles', CreateRole::class)->name('roles');
    Route::get('/view_transactions/{transactions}', ViewTransaction::class)->name('view_transactions');
});
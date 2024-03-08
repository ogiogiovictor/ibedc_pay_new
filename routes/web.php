<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Login;
use App\Livewire\Dashboard;
use App\Livewire\Transactions;
use App\Livewire\Users;
use App\Livewire\Wallets;

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
Route::get('/dashboard', Dashboard::class);
Route::get('/transactions', Transactions::class);
Route::get('/users', Users::class);
Route::get('/wallet_users', Wallets::class);
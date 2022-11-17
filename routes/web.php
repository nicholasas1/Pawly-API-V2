<?php

use Illuminate\Support\Facades\Route;
//use App\Http\Controllers\mail;
use App\Http\Controllers\MailServer;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Mail;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/ReminderPayment', function () {
    return view('InvoicePendingPayment');
});

Route::get('migrate-fresh', function () {
    $exitCode = Artisan::call('migrate:fresh --seed --force');
});

Route::get('migrate', function () {
    $exitCode = Artisan::call('migrate --seed --force');
});

Route::get('/sendActivateMail', [MailServer::class, 'index']);

Route::get('/lala', function () {
    return view('AccountActive');
});

Route::get('profile/ActivateAccount', [UserController::class, 'ActivateEmail']);

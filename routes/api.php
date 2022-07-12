<?php

use App\Http\Controllers\DoctorController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\SplashscreenMobileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserpetsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('profile/getlist', [UserController::class, 'getlist']);

Route::post('profile/login', [UserController::class, 'login']);

Route::post('profile/user_registration', [UserController::class, 'register']);

Route::post('upload_base_64', [UserController::class,'uploadBase64']);

Route::post('profile/update_query', [UserController::class, 'update_query']);

Route::post('cms/update_token', [UserController::class, 'update_token']);

Route::post('profile/sosmedlogin', [UserController::class, 'sosmedlogin']);

Route::get('mobile/getSplash', [SplashscreenMobileController::class, 'getSplash']);

Route::post('mobile/deleteSplash', [SplashscreenMobileController::class, 'deleteSplash']);

Route::post('mobile/createSplash', [SplashscreenMobileController::class, 'createSplash']);

Route::post('mobile/updateSplash', [SplashscreenMobileController::class, 'updateSplash']);

Route::post('doctor/registration', [DoctorController::class, 'regisasdoctor']);

Route::get('doctor/getdetaildoctor', [DoctorController::class, 'getlistdoctor']);

Route::post('doctor/updatedoctor', [DoctorController::class, 'updatedoctor']);

Route::post('doctor/adddoctorspeciality', [DoctorController::class, 'adddoctorspeciality']);

Route::post('doctor/updatedoctorspeciality', [DoctorController::class, 'updatedoctorspeciality']);

Route::post('doctor/deletedoctor', [DoctorController::class, 'deletedoctorlist']);

Route::post('doctor/deletedoctorspeciality', [DoctorController::class, 'deletedoctorspeciality']);

Route::post('pet/addpet', [UserpetsController::class, 'addpet']);

Route::get('pet/getuserpet', [UserpetsController::class, 'getuserpet']);

Route::get('pet/getpetdetail', [UserpetsController::class, 'getpetdetail']);




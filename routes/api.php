<?php

use App\Http\Controllers\SiswaController;
use App\Http\Controllers\SplashscreenMobileController;
use App\Http\Controllers\UserController;
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


Route::post('profile/update_query', [UserController::class, 'update_query']);

Route::post('cms/update_token', [UserController::class, 'update_token']);

Route::get('mobile/getSplash', [SplashscreenMobileController::class, 'getSplash']);

Route::post('mobile/deleteSplash', [SplashscreenMobileController::class, 'deleteSplash']);

Route::post('mobile/createSplash', [SplashscreenMobileController::class, 'createSplash']);

Route::post('mobile/updateSplash', [SplashscreenMobileController::class, 'updateSplash']);


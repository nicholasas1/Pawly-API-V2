<?php

use App\Http\Controllers\ClinicController;
use App\Http\Controllers\CouponserviceController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\FavController;
use App\Http\Controllers\JWTValidator;
use App\Http\Controllers\MobileBannerController;
use App\Http\Controllers\otpController;
use App\Http\Controllers\PaymentmethController;
use App\Http\Controllers\RatingsController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\SplashscreenMobileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserpetsController;
use App\Http\Controllers\WalletController;
use App\Models\couponservice;
use App\Models\fav;
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

Route::get('profile/getuserdetail', [UserController::class, 'getuserdetail']);

Route::post('profile/login', [UserController::class, 'login']);

Route::post('profile/user_registration', [UserController::class, 'register']);

Route::post('upload_base_64', [UserController::class,'uploadBase64']);

Route::post('cms/update_query', [UserController::class, 'update_query']);

Route::post('profile/update_token', [UserController::class, 'update_token']);

Route::post('profile/sosmedlogin', [UserController::class, 'sosmedlogin']);

Route::post('profile/deleteuser', [UserController::class, 'deleteuser']);

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

Route::post('pet/updatepet', [UserpetsController::class, 'updatepet']);

Route::post('pet/deletepet', [UserpetsController::class, 'deletepet']);

Route::post('pet/uploadimage', [UserpetsController::class, 'uploadBase64']);

Route::get('location/autocomplete', [ClinicController::class, 'autocomplete']);

Route::get('location/getplace', [ClinicController::class, 'getplace']);

Route::get('location/getlatlong', [ClinicController::class, 'getlatlong']);

Route::post('doctor/lastonline', [DoctorController::class, 'lastonline']);

Route::get('doctor/filtersearch', [DoctorController::class, 'doctorGetList']);

Route::post('ratings/add', [RatingsController::class, 'addratings']);

Route::post('refresh-token', [JWTValidator::class, 'refreshToken']);

Route::post('logout', [JWTValidator::class, 'logout']);

Route::get('testing', [DoctorController::class, 'doctorGetList']);

Route::post('clinic/addClinic', [ClinicController::class, 'addNewClinic']);

Route::post('wallet/addAmount', [WalletController::class, 'AddAmmount']);

Route::get('wallet/transaction', [WalletController::class, 'WaletTransaction']);

Route::post('cms/wallet/activation', [WalletController::class, 'wallet_activate_param']);

Route::post('wallet/activation', [WalletController::class, 'wallet_activate_token']);

Route::post('notification/send', [MobileBannerController::class, 'notificationdata']);

Route::post('mobilebanner/create', [MobileBannerController::class, 'createbanner']);

Route::post('mobilebanner/edit', [MobileBannerController::class, 'editbanner']);

Route::post('mobilebanner/togglebanner', [MobileBannerController::class, 'togglebanner']);

Route::post('mobilebanner/deletebanner', [MobileBannerController::class, 'deletebanner']);

Route::post('service/fav/add', [FavController::class, 'addfav']);

Route::post('service/fav/delete', [FavController::class, 'deletefav']);

Route::get('service/fav/userlist', [FavController::class, 'getuserfavlist']);

Route::get('cms/user-detail', [UserController::class, 'getuserdetailParam']);

Route::post('role/add-admin', [RoleController::class, 'adminRole']);

Route::post('role/delete-role', [RoleController::class, 'deleteRole']);

Route::post('rrole/add-role', [RoleController::class, 'addRole']);

Route::get('role/user-role', [RoleController::class, 'userRole']);

Route::post('cms/user/activate', [UserController::class, 'activateAccount']);

Route::post('otp/create', [otpController::class, 'makeOTP']);

Route::post('otp/validate', [otpController::class, 'validateOTP']);

Route::post('otp/resend', [otpController::class, 'resend']);

Route::post('payment/methods', [PaymentmethController::class,'payment_method']);

Route::post('coupons/check', [CouponserviceController::class,'coupon_service']);

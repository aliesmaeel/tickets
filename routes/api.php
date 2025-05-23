<?php


use App\Http\Controllers\API\AdvertisementsController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\CityController;
use App\Http\Controllers\API\CustomerAuthController;
use App\Http\Controllers\API\CustomerProfileController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\EventController;

use  App\Traits\ApiResponse;

Route::fallback(function () {
    return response(
        [
            'success' => false,
            'message' => 'API Not Found',
            'data' => null,
            'error_code' => 1,
        ], 404
    );
});

Route::post('/customers/ads', [AdvertisementsController::class, 'getAds']);


Route::prefix('customers')->controller(CustomerAuthController::class)->group(function () {
    Route::post('register/request', 'registerRequest');
    Route::post('register/verify', 'registerVerify');
    Route::post('login', 'login');
    Route::post('forgot-password', 'forgotPassword');
    Route::post('reset-password', 'resetPassword');
});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/customers/profile', [CustomerProfileController::class, 'getProfile']);
    Route::post('/customers/profile', [CustomerProfileController::class, 'updateProfile']);
    Route::get('/customers/categories', [CategoryController::class, 'getCategories']);
    Route::get('/customers/cities', [CityController::class, 'getCities']);
    Route::put('/customers/profile', [CustomerProfileController::class, 'updateProfile']);
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{id}', [EventController::class, 'show']);

    Route::get('/event-seats/{id}', [EventController::class, 'getEventSeats']);
    Route::post('/orders', [OrderController::class, 'createOrder']);
    Route::post('/apply-coupon', [CouponController::class, 'apply']);
    Route::post('/wallet/convert-points', [WalletController::class, 'convertPointsToMoney']);


});



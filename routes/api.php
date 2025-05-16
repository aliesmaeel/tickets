<?php


use App\Http\Controllers\API\CustomerAuthController;
use App\Http\Controllers\API\CustomerProfileController;
use Illuminate\Support\Facades\Route;


Route::prefix('customers')->controller(CustomerAuthController::class)->group(function () {
    Route::post('register/request', 'registerRequest');
    Route::post('register/verify', 'registerVerify');
    Route::post('login', 'login');
    Route::post('forgot-password', 'forgotPassword');
    Route::post('reset-password', 'resetPassword');
});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/customers/profile', [CustomerProfileController::class, 'getProfile']);
    Route::put('/customers/profile', [CustomerProfileController::class, 'updateProfile']);
});


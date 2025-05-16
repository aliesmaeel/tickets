<?php


use App\Http\Controllers\API\CustomerAuthController;
use Illuminate\Support\Facades\Route;


Route::prefix('customers')->controller(CustomerAuthController::class)->group(function () {
    Route::post('register/request', 'registerRequest');
    Route::post('register/verify', 'registerVerify');
    Route::post('login', 'login');
    Route::post('forgot-password', 'forgotPassword');
    Route::post('reset-password', 'resetPassword');

});


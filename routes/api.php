<?php

use App\Http\Controllers\API\OtpController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;


Route::post('/register/request', [AuthController::class, 'registerRequest']);
Route::post('/register/verify', [AuthController::class, 'registerVerify']);



Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', function () {
        return auth()->user();
    });
});

<?php

use App\Filament\Pages\CreateEventSeatsGrid;
use App\Http\Controllers\SeatController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin/login');
});


Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/get-seat-classes/{eventId}', [CreateEventSeatsGrid::class, 'getSeatClassesAjax']);
    Route::post('/store-event-seats', [SeatController::class, 'store']);

});

<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin/login');
});


Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/get-seat-classes/{eventId}', [\App\Filament\Pages\CreateEventSeatsGrid::class, 'getSeatClassesAjax']);
});

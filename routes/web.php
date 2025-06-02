<?php

use App\Filament\Pages\CreateEventSeatsGrid;
use App\Http\Controllers\SeatController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');

});


Route::get('/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'ar', 'kur']))
    {
        session(['locale' => $locale]);
        App::setLocale($locale);
    }
    return view('welcome');
});



Route::middleware(['web', 'auth'])->group(function () {

    Route::get('/get-seat-classes/{eventId}', [CreateEventSeatsGrid::class, 'getSeatClassesAjax']);

    Route::delete('/event-seats/{event}', [SeatController::class, 'destroy'])->name('event-seats.delete');
    Route::get('/edit-event-seats/{id}', [SeatController::class, 'edit'])->name('event-seats.edit');
    Route::post('/update-event-seats', [SeatController::class, 'update'])->name('event-seats.update');

    Route::get('/get-event-seats/{event_id}', [SeatController::class, 'getEventSeats']);
    Route::post('/store-event-seats', [SeatController::class, 'store']);

});

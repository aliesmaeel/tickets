<?php

namespace App\Providers;

use App\Models\EventSeat;
use App\Models\SeatClass;
use App\Observers\GenerateDefaultSeatsObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        SeatClass::observe(GenerateDefaultSeatsObserver::class);

    }

}

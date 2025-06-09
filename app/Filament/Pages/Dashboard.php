<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CustomersChart;
use App\Filament\Widgets\OrderChart;
use App\Support\StaticPermissions;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            CustomersChart::class,
            OrderChart::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [];
    }


    public static function canAccess(): bool
    {
        return Auth::user()?->can(StaticPermissions::DASHBOARD);
    }

}




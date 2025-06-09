<?php

namespace App\Filament\Pages;

use App\Support\StaticPermissions;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class EditEventSeatsGrid extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.edit-event-seats-grid';
    protected static ?string $navigationGroup = 'Seat Management';


    protected static ?int $navigationSort = 3;

    public $event_id;
    public $redirectTo = null;

    public static function shouldRegisterNavigation(): bool
    {
        return false; // Hides it from the sidebar
    }


    public function mount(): void
    {
        $this->event_id = request()->get('event_id');

    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can(StaticPermissions::EDIT_EVENT_SEATS_GRID);
    }

}

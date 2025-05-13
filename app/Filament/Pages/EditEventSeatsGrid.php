<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

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

}

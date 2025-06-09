<?php

namespace App\Filament\Pages;

use App\Models\Event;
use App\Support\StaticPermissions;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ViewEventSeats extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.view-event-seats';

    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup = 'Seat Management';

    public function getEventsWithSeatData(): Collection
    {
        $event =Event::with('seats')->get();

        $event= $event
            ->map(function ($event) {

                return [
                    'id' => $event->id,
                    'name' => $event->name['en'] ?? $event->name,
                    'rows' => $event->seats->max('row')+1 ==1 ? 0 : $event->seats->max('row')+1,
                    'cols' =>  $event->seats->max('col')+1 ==1 ? 0 : $event->seats->max('col')+1,
                ];
            });

        return $event;
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can(StaticPermissions::VIEW_EVENT_SEATS);
    }
}

<?php

namespace App\Filament\Pages;

use App\Models\Event;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class ViewEventSeats extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.view-event-seats';

    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup = 'Seat Management';

    public function getEventsWithSeatData(): Collection
    {
        $event= Event::withCount(['seats'])
        ->get()
            ->map(function ($event) {

                return [
                    'id' => $event->id,
                    'name' => $event->name['en'] ?? $event->name,
                    'rows' => sqrt($event->seats_count),
                    'cols' =>  sqrt($event->seats_count),
                ];
            });

        return $event;
    }
}

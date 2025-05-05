<?php

namespace App\Filament\Resources\EventSeatResource\Pages;

use App\Filament\Resources\EventSeatResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEventSeats extends ListRecords
{
    protected static string $resource = EventSeatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

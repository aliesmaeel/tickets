<?php

namespace App\Filament\Resources\SeatClassResource\Pages;

use App\Filament\Resources\SeatClassResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSeatClasses extends ListRecords
{
    protected static string $resource = SeatClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

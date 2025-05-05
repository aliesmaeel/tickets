<?php

namespace App\Filament\Resources\EventSeatResource\Pages;

use App\Filament\Resources\EventSeatResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEventSeat extends EditRecord
{
    protected static string $resource = EventSeatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

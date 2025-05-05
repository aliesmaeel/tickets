<?php

namespace App\Filament\Resources\SeatClassResource\Pages;

use App\Filament\Resources\SeatClassResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSeatClass extends EditRecord
{
    protected static string $resource = SeatClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

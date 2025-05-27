<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Filament\Widgets\GenderDistributionChart;
use Filament\Resources\Pages\ViewRecord;
use Filament\Widgets\WidgetConfiguration;

class ViewEvent extends ViewRecord
{
    protected static string $resource = EventResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            GenderDistributionChart::class,
        ];
    }

}

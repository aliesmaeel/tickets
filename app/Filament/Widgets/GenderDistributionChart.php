<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;
use App\Models\Ticket;

class GenderDistributionChart extends ChartWidget
{
    public ?Model $record = null;
    protected static ?string $heading = 'Gender Attend Distribution';

    protected function getData(): array
    {
        if (!$this->record) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $attendees = Ticket::where('event_id', $this->record->id)
            ->where('status', 'attended')
            ->with('customer')
            ->get();

        $genderCounts = $attendees->groupBy(fn ($ticket) => $ticket->customer->gender ?? 'unknown')->map->count();

        return [
            'datasets' => [
                [
                    'label' => 'Attendees',
                    'data' => array_values($genderCounts->toArray()),
                    'backgroundColor' => ['#36A2EB', '#FF6384', '#FFCE56'],
                ],
            ],
            'labels' => array_keys($genderCounts->toArray()),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}

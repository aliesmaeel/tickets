<?php


namespace App\Filament\Widgets;

use App\Models\EventSeat;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;
use App\Models\Ticket;
use App\Models\Event;

class SeatAvailabilityChart extends ChartWidget
{
    public ?Model $record = null; // Injected automatically

    protected static ?string $heading = 'Seat Availability';

    protected function getData(): array
    {
        if (!$this->record || !($this->record instanceof Event)) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $reservedSeats = EventSeat::where('event_id', $this->record->id)
            ->where('status', 'Reserved')
            ->count();

        $availableSeats = EventSeat::where('event_id', $this->record->id)
            ->where('status', 'Available')
            ->count();


        return [
            'datasets' => [
                [
                    'label' => 'Seats',
                    'data' => [$reservedSeats, $availableSeats],
                    'backgroundColor' => ['#FF6384', '#36A2EB'],
                ],
            ],
            'labels' => ['Reserved', 'Available'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}

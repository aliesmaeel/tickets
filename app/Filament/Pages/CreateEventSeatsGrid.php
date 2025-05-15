<?php

namespace App\Filament\Pages;

use App\Models\SeatClass;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Models\Event;
use Illuminate\Http\JsonResponse;

class CreateEventSeatsGrid extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationGroup = 'Seat Management';

    protected static ?int $navigationSort= 1;

    protected static ?string $navigationIcon = 'heroicon-o-plus';
    protected static string $view = 'filament.pages.create-event-seats-grid';

    public ?int $rows = null;
    public ?int $cols = null;
    public ?int $event_id = null;
    public array $grid = [];

    public function getSeatClassesAjax($eventId): JsonResponse
    {
        $classes = \App\Models\SeatClass::where('event_id', $eventId)
            ->get(['id', 'name','price','color']);
        return response()->json($classes);
    }

    public function generateGrid(): void
    {
        $this->validate([
            'rows' => 'required|integer|min:1',
            'cols' => 'required|integer|min:1',
            'event_id' => 'required|exists:events,id',
        ]);

        $this->grid = [];

        for ($i = 0; $i < $this->rows; $i++) {
            for ($j = 0; $j < $this->cols; $j++) {
                $this->grid[$i][$j] = "R{$i}C{$j}";
            }
        }
    }

    public function getEventsProperty()
    {
        return Event::all()->mapWithKeys(function ($event) {
            return [$event->id => $event->name['en'] ?? $event->name];
        })->toArray();
    }



}

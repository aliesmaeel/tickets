<?php

namespace App\Filament\Pages;

use App\Models\EventSeat;
use Filament\Pages\Page;
use Livewire\WithPagination;

class EventSeatsOverview extends Page
{
    use WithPagination;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Seat Management';
    protected static string $view = 'filament.pages.event-seats-overview';
    protected static ?int $navigationSort = 0;

    public $seatData = [];

    public function mount(): void
    {
        $this->loadSeats();
    }

    public function loadSeats()
    {
        $this->seatData = EventSeat::with('event')->get()->map(function ($seat) {
            $data = $seat->data ?? [];
            $maxRow = collect($data)->pluck('row')->max() ?? 0;
            $maxCol = collect($data)->pluck('col')->max() ?? 0;

            return [
                'id' => $seat->id,
                'name' => $seat->event->name['en'] ?? 'N/A',
                'rows' => $maxRow + 1,
                'cols' => $maxCol + 1,
            ];
        })->toArray();
    }

    public function deleteSeat($id)
    {
        EventSeat::findOrFail($id)->delete();
        $this->loadSeats(); // Refresh data after deletion
        session()->flash('message', 'Seat deleted successfully.');
    }

    public function editSeat($id)
    {
        dd("Edit Seat ID: " . $id);
        // Or redirect to edit page if\Livewire\WithPagination}
    }
}

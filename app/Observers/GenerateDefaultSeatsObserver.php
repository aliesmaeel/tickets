<?php

namespace App\Observers;

use App\Models\SeatClass;
use Illuminate\Support\Facades\DB;

class GenerateDefaultSeatsObserver
{

    public function creating(SeatClass $seatClass)
    {
        $eventId = $seatClass->event_id;

        $existingStageSeats = SeatClass::where('event_id', $eventId)
            ->whereIn('name', ['stage', 'empty'])
            ->exists();


        if (! $existingStageSeats) {
            DB::table('seat_classes')->insert([
                ['name' => 'empty', 'price' => 0, 'color' => '#000000', 'event_id' => $eventId, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'stage', 'price' => 0, 'color' => '#a6a2a2', 'event_id' => $eventId, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\EventSeat;
use App\Models\SeatClass;
use Illuminate\Http\Request;

class SeatController extends Controller
{
    public function store(Request $request)
    {

        $request->validate([
            'event_id' => 'required|exists:events,id',
            'data' => 'required|array',
            'data.seats' => 'required|array',
        ]);

       $oldEventSeats = EventSeat::where('event_id', $request->event_id)->delete();

        foreach ($request->data['seats'] as $seat) {
            EventSeat::create([
                'event_id' => $request->event_id,
                'seat_class_id' => $seat['seat_class_id'],
                'row' => $seat['row'],
                'col' => $seat['col'],
                'status' => $this->getSeatStatus($seat['seat_class_name']),
            ]);
        }


        return response()->json(['success' => true]);
    }


    public function getSeatStatus(String $seatName)
    {
        if ($seatName == 'stage' || $seatName =='empty'){
            return 'Blocked';
        }
        return 'Available';
    }



}

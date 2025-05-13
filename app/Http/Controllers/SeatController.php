<?php

namespace App\Http\Controllers;

use App\Models\Event;
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

    public function destroy(Event $event)
    {
        $event->seats()->delete();
        $event->save();

        return redirect()->back()->with('success', 'Event seats deleted.');
    }

    public function edit($eventId)
    {
        $event = Event::findOrFail($eventId);
        $seatClasses = SeatClass::where('event_id', $eventId)->get();
        $seats = $event->seats()->get(); // Assuming you have relation `seats()`

        return view('filament.pages.edit-event-seats-grid', [
            'event' => $event,
            'seatClasses' => $seatClasses,
            'seats' => $seats,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'updates' => 'required|array',
        ]);

        foreach ($validated['updates'] as $seatData) {
            \App\Models\EventSeat::updateOrCreate(
                [
                    'event_id' => $validated['event_id'],
                    'row' => $seatData['row'],
                    'col' => $seatData['col'],
                ],
                [
                    'seat_class_id' => $seatData['seat_class_id'],
                ]
            );
        }

        return response()->json(['success' => true]);
    }

    public function getEventSeats($id)
    {
        $event = Event::withCount('seats')->findOrFail($id);

        $data= response()->json([

            'rows' =>  sqrt($event->seats_count),
            'cols' => sqrt($event->seats_count),
            'seats' => $event->seats->map(function ($seat) {
                return [
                    'row' => $seat->row,
                    'col' => $seat->col,
                    'seat_class_id' => $seat->seat_class_id,
                    'seat_class_name' => $seat->seatClass->name,
                    'color' => $seat->seatClass->color
                ];
            }),
        ]);

        return $data;
    }
    public function getSeatClasses($event_id)
    {
        dd($event_id);
        return response()->json(SeatClass::where('event_id', $event_id)->get());
    }


}

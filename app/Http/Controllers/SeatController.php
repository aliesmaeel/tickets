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
        $seats = $event->seats()->get();

        return view('filament.pages.edit-event-seats-grid', [
            'event' => $event,
            'seatClasses' => $seatClasses,
            'seats' => $seats,
        ]);
    }

    public function getSeatFromSeatClassId($seatClassId)
    {
        $seatClass = SeatClass::find($seatClassId);
        return $seatClass ? $this->getSeatStatus($seatClass->name) : null;
    }
    public function update(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'data.seats' => 'array',
        ]);

        $eventId = $validated['event_id'];
        $submittedSeats = collect($validated['data']['seats']);


        foreach ($submittedSeats as $seatData) {

            if( isset($seatData['status']) && $seatData['status']== 'Reserved'){
               continue;
            }else{

                EventSeat::updateOrCreate(
                    [
                        'event_id' => $eventId,
                        'row' => $seatData['row'],
                        'col' => $seatData['col'],

                    ],
                    [
                        'seat_class_id' => $seatData['seat_class_id'],
                        'status' => $this->getSeatFromSeatClassId($seatData['seat_class_id']),
                    ]
                );
            }

        }

        $submittedKeys = $submittedSeats->map(function ($seat) {
            return $seat['row'] . '-' . $seat['col'];
        });

        EventSeat::where('event_id', $eventId)
            ->get()
            ->each(function ($seat) use ($submittedKeys) {
                $key = $seat->row . '-' . $seat->col;
                if (!$submittedKeys->contains($key)) {
                    $seat->delete();
                }
            });

        return response()->json(['success' => true]);
    }


    public function getEventSeats($id)
    {
        $event = Event::with('seats.seatClass')->findOrFail($id);

        $maxRow = $event->seats->max('row')+1;
        $maxCol = $event->seats->max('col')+1;

        return response()->json([
            'rows' => $maxRow,
            'cols' => $maxCol,
            'seats' => $event->seats->map(function ($seat) {

                return [
                    'row' => $seat->row,
                    'col' => $seat->col,
                    'seat_class_id' => $seat->seat_class_id,
                    'seat_class_name' => $seat->seatClass->name ?? null,
                    'color' => $seat->seatClass->color ?? null,
                    'status' => $seat->status,
                ];
            }),
        ]);
    }

    public function getSeatClasses($event_id)
    {
        return response()->json(SeatClass::where('event_id', $event_id)->get());
    }


}

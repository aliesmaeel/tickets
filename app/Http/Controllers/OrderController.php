<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventSeat;
use App\Models\Order;
use App\Models\SeatClass;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    use ApiResponse;
    public function createOrder(Request $request)
    {
        App::setLocale(auth()->user()->lang);

        $request->validate([
            'seats' => 'required|array|min:1',
            'seats.*.id' => 'required|integer|exists:event_seats,id',
            'seats.*.event_id' => 'required|integer|exists:events,id',
        ]);




        $customer = auth()->user();

        $groupedSeats = collect($request->seats)->groupBy('event_id');

        $createdOrders = [];
        $reservedSeats = [];

        DB::beginTransaction();

        try {
            foreach ($groupedSeats as $eventId => $seatsGroup) {
                $seatIds = collect($seatsGroup)->pluck('id');

                $seats = EventSeat::whereIn('id', $seatIds)
                    ->where('event_id', $eventId)
                    ->with('seatClass')
                    ->lockForUpdate()
                    ->get();

                $alreadyReserved = $seats->filter(fn($seat) => strtolower($seat->status) !== 'available');

                if ($alreadyReserved->isNotEmpty()) {
                    $reservedSeats = array_merge($reservedSeats, $alreadyReserved->pluck('id')->toArray());
                    continue;
                }

                $totalPrice = $seats->sum(fn($seat) => $seat->Seatclass->price ?? 0);
                $totalPrice = number_format($totalPrice, 2, '.', '');

                $order = Order::create([
                    'customer_id' => $customer->id,
                    'event_id' => $eventId,
                    'total_price' => $totalPrice,
                ]);

                $order->seats()->attach($seatIds);

                EventSeat::whereIn('id', $seatIds)->update(
                    [
                        'status' => 'Reserved',
                        'seat_class_id' => $this->getReservedSeatClassId($eventId),

                    ]
                );

                $createdOrders[] = [
                    'order_id' => $order->id,
                    'event_id' => $eventId,
                    'total_price' => $totalPrice,
                    'seat_ids' => $seatIds,
                ];
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($createdOrders) > 0
                    ? 'Orders processed successfully.'
                    : 'No orders were created.',
                'data' => [
                    'created_orders' => $createdOrders,
                    'reserved_seats' => $reservedSeats,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Order creation failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create orders.',
                'data' => [
                    'created_orders' => $createdOrders,
                    'reserved_seats' => $reservedSeats,
                    'error' => $e->getMessage(), // optional for debugging
                ],
            ], 500);
        }
    }


    public function getReservedSeatClassId($eventId)
    {
        $event = Event::findOrFail($eventId);
        $seatClass = SeatClass::where('event_id', $eventId)->where('name', 'reserved')->first();

        return $seatClass ? $seatClass->id : null;
    }

}

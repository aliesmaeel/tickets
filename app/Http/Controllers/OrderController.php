<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Event;
use App\Models\EventSeat;
use App\Models\Order;
use App\Models\SeatClass;
use App\Models\Setting;
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
            'coupon_code' => 'nullable|string|exists:coupons,code',
        ]);


        $customer = auth()->user();

        $coupon = null;
        $discount = 0;
        if ($request->filled('coupon_code')) {
            $coupon = Coupon::where('code', $request->coupon_code)
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->where(function ($q) {
                    $q->whereNull('max_uses')
                        ->orWhereColumn('used_count', '<', 'max_uses');
                })
                ->first();


            if (!$coupon) {
                return $this->respondError(
                    __('messages.coupon_not_valid'),
                    null,
                    422
                );
            }
        }


        $groupedSeats = collect($request->seats)->groupBy('event_id');

        $createdOrders = [];
        $conflictingSeats = [];

        DB::beginTransaction();

        try {
            foreach ($groupedSeats as $eventId => $seatsGroup) {
                $seatIds = collect($seatsGroup)->pluck('id');

                $lockedSeats = EventSeat::whereIn('id', $seatIds)
                    ->where('event_id', $eventId)
                    ->with('seatClass')
                    ->lockForUpdate()
                    ->get();

                $alreadyReservedSeats = $lockedSeats->filter(
                    fn($seat) => strtolower($seat->status) !== 'available'
                );

                if ($alreadyReservedSeats->isNotEmpty()) {
                    $conflictingSeats = array_merge(
                        $conflictingSeats,
                        $alreadyReservedSeats->pluck('id')->toArray()
                    );
                    continue;
                }

                $basePrice = $lockedSeats->sum(fn($seat) => $seat->seatClass->price ?? 0);
                $discount = 0;

                if ($coupon) {
                    if ($coupon->type === 'fixed') {
                        $discount = min($coupon->value, $basePrice);
                    } elseif ($coupon->type === 'percentage') {
                        $discount = $basePrice * ($coupon->value / 100);
                    }

                    $coupon->increment('used_count');
                }

                $totalPrice = $basePrice - $discount;
                $totalPrice = number_format($totalPrice, 2, '.', '');

                $rate = Setting::getRate('money_to_point_rate');

                $order = Order::create([
                    'customer_id' => $customer->id,
                    'event_id' => $eventId,
                    'total_price' => $totalPrice,
                    'base_price' => $basePrice,
                    'money_to_point_rate' => $rate,
                    'coupon_id' => $coupon?->id,
                    'discount_value' => $discount,
                ]);


                $pointsEarned = $order->total_price * $rate;

                $customer->wallet->increment('points', (int) $pointsEarned);

                $order->seats()->attach($seatIds);

                EventSeat::whereIn('id', $seatIds)->update([
                    'status' => 'Reserved',
                ]);

                $createdOrders[] = [
                    'order_id' => $order->id,
                    'event_id' => $eventId,
                    'base_price' => $basePrice,
                    'discount' => $discount,
                    'total_price' => $totalPrice,
                    'booked_seat_ids' => $seatIds,
                    'applied_coupon' => $coupon?->code,
                ];

            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($createdOrders) > 0
                    ? __('messages.order_created_successfully')
                    : __('messages.some_seats_already_reserved'),
                'data' => [
                    'created_orders' => $createdOrders,
                    'conflicting_seat_ids' => $conflictingSeats,
                    'wallet_points' => $customer->wallet->points,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Order creation failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => __('messages.order_creation_failed'),
                'data' => [
                    'created_orders' => $createdOrders,
                    'conflicting_seat_ids' => $conflictingSeats,
                    'error' => $e->getMessage(),
                ],
            ], 500);
        }
    }



    public function getReservedSeatClassId($eventId)
    {
        $seatClass = SeatClass::where('event_id', $eventId)->where('name', 'reserved')->first();
        return $seatClass ? $seatClass->id : null;
    }

}

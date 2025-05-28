<?php

namespace App\Http\Controllers;

use App\Http\Resources\Api\TicketResource;
use App\Models\Coupon;
use App\Models\Event;
use App\Models\EventSeat;
use App\Models\Order;
use App\Models\SeatClass;
use App\Models\Setting;
use App\Models\Ticket;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Milon\Barcode\DNS1D;

class OrderController extends Controller
{
    use ApiResponse;
    public function createOrder(Request $request)
    {
        App::setLocale(auth()->user()->lang);

        $request->validate([
            'seats' => 'required|array|min:1',
            'seats.*' => 'required|integer|exists:event_seats,id',
            'event_id' => 'required|integer|exists:events,id',
            'coupon_code' => 'nullable|string|exists:coupons,code',
            'reservation_type' => 'required|in:Cache,Epay',
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
                return $this->respondError(__('messages.coupon_not_valid'), null, 422);
            }
        }

        $eventId = $request->event_id;
        $seatIds = collect($request->seats);

        $createdOrders = [];
        $conflictingSeats = [];

        DB::beginTransaction();

        try {
            $lockedSeats = EventSeat::whereIn('id', $seatIds)
                ->where('event_id', $eventId)
                ->with('seatClass')
                ->lockForUpdate()
                ->get();

            $alreadyReservedSeats = $lockedSeats->filter(
                fn($seat) => strtolower($seat->status) !== 'available'
            );

            if ($alreadyReservedSeats->isNotEmpty()) {
                $conflictingSeats = $alreadyReservedSeats->pluck('id')->toArray();
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => __('messages.some_seats_already_reserved'),
                    'data' => [
                        'conflicting_seat_ids' => $conflictingSeats,
                        'created_orders' => [],
                    ],
                ]);
            }

            $basePrice = $lockedSeats->sum(fn($seat) => $seat->seatClass->price ?? 0);
            if ($coupon) {
                $discount = $coupon->type === 'fixed'
                    ? min($coupon->value, $basePrice)
                    : $basePrice * ($coupon->value / 100);

                $coupon->increment('used_count');
            }

            $totalPrice = number_format($basePrice - $discount, 2, '.', '');
            $rate = Setting::getRate('money_to_point_rate');

            $order = Order::create([
                'customer_id' => $customer->id,
                'event_id' => $eventId,
                'total_price' => $totalPrice,
                'base_price' => $basePrice,
                'money_to_point_rate' => $rate,
                'coupon_id' => $coupon?->id,
                'discount_value' => $discount,
                'reservation_type' => $request->reservation_type,
                'reservation_status' => $request->reservation_type === 'Epay',
            ]);

            $pointsEarned = $order->total_price * $rate;
            $customer->wallet->increment('points', (int) $pointsEarned);

            $order->seats()->attach($seatIds);

            EventSeat::whereIn('id', $seatIds)->update([
                'status' => 'Reserved',
            ]);

            $orderSeats = DB::table('order_seat')
                ->where('order_id', $order->id)
                ->whereIn('event_seat_id', $seatIds)
                ->get();

            foreach ($orderSeats as $orderSeat) {
                $ticketCode = $this->generateTicketCode($order->id, $orderSeat->id);
                Ticket::create([
                    'order_id' => $order->id,
                    'order_seat_id' => $orderSeat->id,
                    'status' => 'upcoming',
                    'customer_id' => $customer->id,
                    'event_id' => $eventId,
                ]);
            }

            $createdOrders[] = [
                'order_id' => $order->id,
                'event_id' => $eventId,
                'base_price' => $basePrice,
                'discount' => $discount,
                'total_price' => $totalPrice,
                'booked_seat_ids' => $seatIds,
                'applied_coupon' => $coupon?->code,
            ];

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('messages.order_created_successfully'),
                'data' => [
                    'created_orders' => $createdOrders,
                    'conflicting_seat_ids' => $conflictingSeats,
                    'wallet_points' => $customer->wallet->points,
                ],
            ]);

        } catch (Exception $e) {
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

    protected function generateTicketCode(int $orderId, int $orderSeatId): string
    {
        $seed = $orderId . '-' . $orderSeatId . '-' . now()->timestamp;
        return strtoupper(substr(sha1($seed), 0, 12));
    }




}

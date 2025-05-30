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
use App\Services\PaymentService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Milon\Barcode\DNS1D;
use App\Helpers\WalletHelper;
class OrderController extends Controller
{
    use ApiResponse;

    public function createOrder(Request $request)
    {
        App::setLocale(auth()->user()->lang);

        $customerHasMoreThanOneCacheOrder = Order::where('customer_id', auth()->id())
            ->where('reservation_type', 'Cache')
            ->where('reservation_status', true)
            ->where('event_id', $request->event_id)
            ->exists();

        if ($customerHasMoreThanOneCacheOrder){
            return $this->respondError(__('messages.max_allowed_cache_orders_reached'), null, 422);
        }

        $request->validate([
            'seats' => 'required|array|min:1',
            'seats.*' => 'required|integer|exists:event_seats,id',
            'event_id' => 'required|integer|exists:events,id',
            'coupon_code' => 'nullable|string|exists:coupons,code',
            'reservation_type' => 'required|in:Cache,Epay',
            'split_payment' => 'required|boolean',
        ]);

        $customer = auth()->user();
        $coupon = null;
        $discount = 0;
        $discountFromWallet = 0;

        if ($request->filled('coupon_code')) {
            $coupon = Coupon::findValidCoupon($request->coupon_code);
            if (!$coupon) {
                return $this->respondError(__('messages.coupon_not_valid'), null, 422);
            }
        }

        $eventId = $request->event_id;
        $seatIds = collect($request->seats);
        DB::beginTransaction();

        try {
            $lockedSeats = EventSeat::whereIn('id', $seatIds)
                ->where('event_id', $eventId)
                ->with('seatClass')
                ->lockForUpdate()
                ->get();

            $alreadyReservedSeats = $lockedSeats->filter(fn($s) => strtolower($s->status) !== 'available');
            if ($alreadyReservedSeats->isNotEmpty()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => __('messages.some_seats_already_reserved'),
                    'data' => ['conflicting_seat_ids' => $alreadyReservedSeats->pluck('id')->toArray()],
                ]);
            }

            $basePrice = $lockedSeats->sum(fn($seat) => $seat->seatClass->price ?? 0);

            if ($coupon) {
                $discount = $basePrice - $coupon->applyDiscount($basePrice);
            }

            $priceAfterDiscount = $basePrice - $discount;
            $wallet = $customer->wallet;
            $walletMoney = $wallet->balance;
            $walletCanCover = WalletHelper::checkWalletHasEnoughMoney($wallet, $priceAfterDiscount);

            $reservationStatus = false;
            $reservationType = $request->reservation_type;


            if (!$request->split_payment) {
                if ($reservationType === 'Epay') {
                    if (PaymentService::charge($priceAfterDiscount)) {
                        $reservationStatus = true;
                        $wallet->increment('points', (int)($priceAfterDiscount * Setting::getRate('money_to_point_rate')));
                    } else {
                        DB::rollBack();
                        return $this->respondError(__('messages.no_enough_money_card'));
                    }
                }
            } else {
                if ($reservationType === 'Epay') {

                    if ($walletCanCover) {
                        $discountFromWallet = $priceAfterDiscount;
                        $wallet->decrement('balance', $discountFromWallet);
                        $wallet->increment('points', (int)($priceAfterDiscount * Setting::getRate('money_to_point_rate')));
                        $reservationStatus = true;
                        $reservationType = 'Wallet';

                    } else {
                        $discountFromWallet = $walletMoney;
                        $remaining = $priceAfterDiscount - $walletMoney;
                        if (PaymentService::charge($remaining)) {
                            $wallet->decrement('balance', $walletMoney);
                            $wallet->increment('points', (int)($priceAfterDiscount * Setting::getRate('money_to_point_rate')));
                            $reservationStatus = true;
                        } else {
                            DB::rollBack();
                            return $this->respondError(__('messages.no_enough_money_card'));
                        }
                    }
                } elseif ($reservationType === 'Cache') {
                    if ($walletCanCover) {

                        $discountFromWallet = $priceAfterDiscount;
                        $wallet->decrement('balance', $discountFromWallet);
                        $wallet->increment('points', (int)($priceAfterDiscount * Setting::getRate('money_to_point_rate')));
                        $reservationStatus = true;
                        $reservationType = 'Wallet';
                    } else {
                        $discountFromWallet = $walletMoney;
                        $wallet->decrement('balance', $walletMoney);
                    }
                }
            }

            $order = Order::create([
                'customer_id' => $customer->id,
                'event_id' => $eventId,
                'total_price' => $priceAfterDiscount,
                'base_price' => $basePrice,
                'money_to_point_rate' => Setting::getRate('money_to_point_rate'),
                'coupon_id' => $coupon?->id,
                'discount_coupon' => $discount,
                'discount_wallet_value' => $discountFromWallet,
                'reservation_type' => $reservationType,
                'reservation_status' => $reservationStatus,
            ]);

            $order->seats()->attach($seatIds);
            EventSeat::whereIn('id', $seatIds)->update(['status' => 'Reserved']);

            foreach ($seatIds as $id) {
                $orderSeat = DB::table('order_seat')->where('order_id', $order->id)->where('event_seat_id', $id)->first();
                Ticket::create([
                    'order_id' => $order->id,
                    'order_seat_id' => $orderSeat->id,
                    'status' => 'upcoming',
                    'customer_id' => $customer->id,
                    'event_id' => $eventId,
                ]);
            }

            if ($coupon) {
                $coupon->increment('used_count');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('messages.order_created_successfully'),
                'data' => [
                    'order_id' => $order->id,
                    'base_price' => $basePrice,
                    'discount_coupon' => $discount,
                    'total_price' => $priceAfterDiscount,
                    'discount_wallet_value' => $discountFromWallet,
                    'reservation_type' => $reservationType,
                    'reservation_status' => $reservationStatus,
                    'wallet_points' => $wallet->points,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Order creation failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => __('messages.order_creation_failed'),
                'error' => $e->getMessage(),
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

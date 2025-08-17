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
use App\Services\HyperPayService;
use App\Services\PaymentService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Milon\Barcode\DNS1D;
use App\Helpers\WalletHelper;
class OrderController extends Controller
{
    use ApiResponse;
    public function __construct(protected HyperPayService $hyperPayService) {}

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
        $merchantTransactionId = null;
        $checkoutId = null;
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
                    $merchantTransactionId = uniqid('txn_'). time();
                    $paymentResponse = PaymentService::charge($priceAfterDiscount, $merchantTransactionId);
                    if ($paymentResponse['success']) {
                        $checkoutId = $paymentResponse['id'];
                        $reservationStatus = false;
                        $wallet->increment('points', (int)($priceAfterDiscount * Setting::getRate('money_to_point_rate')));
                    } else {
                        DB::rollBack();
                        return $this->respondError(__($paymentResponse['message']), null, 422);
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
                        $merchantTransactionId = uniqid('txn_'). time();
                        $paymentResponse = PaymentService::charge($priceAfterDiscount, $merchantTransactionId);
                        if ($paymentResponse['success']) {
                            $checkoutId = $paymentResponse['id'];
                            $wallet->decrement('balance', $walletMoney);
                            $wallet->increment('points', (int)($priceAfterDiscount * Setting::getRate('money_to_point_rate')));
                            $reservationStatus = false;
                        } else {
                            DB::rollBack();
                            return $this->respondError(__($paymentResponse['message']), null, 422);
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
                'merchant_transaction_id' => $merchantTransactionId,
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
                    'merchant_transaction_id' => $merchantTransactionId,
                    'checkout_id' => $checkoutId,
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

    public function verifyPayment(Request $request, $merchantTransactionId)
    {
        $data = $this->hyperPayService->verifyPaymentByTransactionId($merchantTransactionId);

        if (!$data || !isset($data['result']['code'])) {
            Log::warning('Payment verification failed or invalid result', ['data' => $data]);
            return response()->json(['status' => 'error', 'message' => 'Payment not verified'], 400);
        }



        $successPattern = '/^(000\.000\.|000\.100\.1|000\.[36]|000\.400\.[1][12]0)/';
        $pendingPattern = '/^(000\.200)/';
        $reviewPattern  = '/^(000\.400\.0[^3]|000\.400\.100)/';
        $rejectPattern = '/^(000\.400\.[1][0-9][1-9]|000\.400\.2|800\.[17]00|800\.800\.[123]|900\.[1234]00|000\.400\.030|800\.[56]|999\.|600\.1|800\.800\.[84]|100\.39[765]|800\.400\.1|700\.[1345][05]0)/';


        $record = $data['records'][0] ??  [];

        $transactionId = $record['id'] ?? null;
        $paymentType = $record['paymentType'] ?? null;
        $brand = $record['paymentBrand'] ?? null;
        $amount = $record['amount'] ?? null;
        $currency = $record['currency'] ?? null;

        if (isset($data['result']['status_code']) && $data['result']['status_code']==404){
            $statusCode= $data['result']['code'] ?? null;
        }else{
            $statusCode = $record['result']['code'] ?? null;
        }

        $statusDescription = $record['result']['description'] ?? null;

        $responseData = [
            'transaction_id'     => $transactionId,
            'payment_type'       => $paymentType,
            'brand'              => $brand,
            'amount'             => $amount,
            'currency'           => $currency,
            'status_code'        => $statusCode,
            'status_description' => $statusDescription,
        ];

        // ---- Decide response type ----
        if (preg_match($successPattern, $statusCode)) {
            Log::info('Payment Verified (Success)', $responseData);

            // Update order if fully captured
            if ($statusCode === '000.100.110') {
                $order = Order::where('merchant_transaction_id', $merchantTransactionId)->first();
                $order?->update(['reservation_status' => true]);
            }

            return response()->json(['status' => 'success'] + $responseData);
        }

        if (preg_match($pendingPattern, $statusCode)) {
            Log::info('Payment Pending', $responseData);

            return response()->json(['status' => 'pending'] + $responseData);
        }

        if (preg_match($reviewPattern, $statusCode)) {
            Log::warning('Payment Pending', $responseData);
            return response()->json(['status' => 'pending'] + $responseData);
        }

        if (preg_match($rejectPattern, $statusCode)) {
            Log::error('Payment Rejected', $responseData);
            return response()->json(['status' => 'reject'] + $responseData);
        }

        // Fallback
        Log::warning('Payment verification unexpected result code', ['code' => $statusCode, 'data' => $data]);
        return response()->json(['status' => 'error', 'message' => 'Unexpected result code', 'data' => $data], 400);
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

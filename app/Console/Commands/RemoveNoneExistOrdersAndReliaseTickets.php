<?php

namespace App\Console\Commands;

use App\Dtos\Fcm\FcmDto;
use App\Dtos\Fcm\FcmReceiverDto;
use App\Http\Controllers\OrderController;
use App\Models\Order;
use App\Notifications\CustomTextNotification;
use App\Services\FcmService;
use App\Services\HyperPayService;
use GPBMetadata\Google\Api\Auth;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Kreait\Firebase\JWT\Contract\Token;

class RemoveNoneExistOrdersAndReliaseTickets extends Command
{
    public function __construct(protected HyperPayService $hyperPayService) {
        parent::__construct();
    }
    protected $signature = 'app:remove-none-exist-orders-and-reliase-tickets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';



    public function handle()
    {
        $orders = Order::whereNotNull('merchant_transaction_id')
            ->where('reservation_status', false)
            ->get();

        foreach ($orders as $order) {

            $data = $this->hyperPayService->verifyPaymentByTransactionId($order->merchant_transaction_id);

            if (!$data || !isset($data['result']['code'])) {
                $this->warn("❌ Payment not verified for txn {$order->merchant_transaction_id}");
                continue;
            }

            if ($data['result']['code'] === '000.000.100') {
                $record = $data['records'][0] ?? [];
                $transactionId = $record['id'] ?? null;
                $paymentType = $record['paymentType'] ?? null;
                $brand = $record['paymentBrand'] ?? null;
                $amount = $record['amount'] ?? null;
                $currency = $record['currency'] ?? null;
                $statusCode = $record['result']['code'] ?? null;
                $statusDescription = $record['result']['description'] ?? null;
                if ($statusCode === '000.100.110') {
                    $order = Order::where('merchant_transaction_id', $order->merchant_transaction_id)->first();
                    $order?->update(['reservation_status' => true]);
                    try {
                        FcmService::sendPushNotification(
                            fcmDto: FcmDto::make(
                                receivers: FcmReceiverDto::make(
                                    id: $order->user->id,
                                    type: $order->user->type
                                ),
                                title: 'Order Verified',
                                body: "Your payment #{$order->id} has been successfully verified.",
                            ));
                    }catch (\Exception $e) {
                    }
                    $this->info("✅ Order #{$order->id} verified and updated.");

                }elseif ($statusCode ==='100.396.103')
                {
                    $order= Order::where('merchant_transaction_id', $order->merchant_transaction_id)->first();
                    $updatedSeats=$order->orderSeats->each(function ($orderSeat) {
                    $orderSeat->eventSeat->update(['status' => 'available']);
                     });
                    try {
                        FcmService::sendPushNotification(
                            fcmDto: FcmDto::make(
                                receivers: FcmReceiverDto::make(
                                    id: $order->user->id,
                                    type: $order->user->type
                                ),
                                title: 'Order Failed',
                                body: "Your payment #{$order->id} has been failed.",));
                    }catch (\Exception $e) {

                    }
                    $order->delete();
                }

            } else {
                $this->warn("⚠️ Unexpected result code for Order #{$order->id}");
            }
        }
    }
}

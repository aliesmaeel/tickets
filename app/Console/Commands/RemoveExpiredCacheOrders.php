<?php

namespace App\Console\Commands;

use App\Models\EventSeat;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveExpiredCacheOrders extends Command
{
    protected $signature = 'orders:cleanup-expired-cache';
    protected $description = 'Delete expired cache orders and release their reserved seats';

    public function handle()
    {
        $now = Carbon::now();

        $expiredOrders = Order::where('reservation_type', 'Cache')
            ->where('reservation_status', false)
            ->whereHas('event', function ($query) use ($now) {
                $query->whereNotNull('time_to_place_cache_order');
            })
            ->get()
            ->filter(function ($order) {
                return $order->created_at->addHours($order->event->time_to_place_cache_order)->lt(now());
            });


        foreach ($expiredOrders as $order) {
            $seatIds = $order->seats()->pluck('event_seat_id');

            // Set seat status to available
            EventSeat::whereIn('id', $seatIds)->update(['status' => 'Available']);
            $customer = $order->customer;
            $customer->wallet->increment('balance', $order->discount_wallet_value);
            if($order->discount_coupon) {
                $order->coupon->decrement('used_count');
            }
            // Delete the order
            $order->delete();
        }

        $this->info("Expired cache orders cleaned up successfully.");
    }
}

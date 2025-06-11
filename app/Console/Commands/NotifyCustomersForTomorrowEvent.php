<?php

namespace App\Console\Commands;

use App\Dtos\Fcm\FcmReceiverDto;
use App\Enums\UserType;
use App\Jobs\SendNotificationToAllCustomer;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Console\Command;

class NotifyCustomersForTomorrowEvent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notify-customers-for-tomorrow-event';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify all customers about events happening tomorrow';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $tomorrowOrders  = Order::whereHas('event' , function ($query) {
                    $query->whereBetween('start_time', [
                        now()->addDay()->startOfDay(),
                        now()->addDay()->endOfDay()
                    ]);
            })
            ->where('reservation_status', 1)
            ->select('id', 'customer_id')
            ->groupBy([
                'id',
                'customer_id'
            ])
            ->get();

        if($tomorrowOrders->isEmpty()) {
            return;
        }

        foreach ($tomorrowOrders as $orderId => $customersIds) {

            $fcmReceivers = [];
            foreach ($customersIds as $customerId) {
                $fcmReceivers[] = FcmReceiverDto::make(
                    id: $customerId,
                    type: UserType::Customer->value
                );
            }

            $title = 'Event Tomorrow';
            $body = 'Don\'t miss out on our event happening tomorrow!';
            $subtitle = 'Event Reminder';

            SendNotificationToAllCustomer::dispatchSync(
                receivers: $fcmReceivers,
                title: $title,
                message: $body,
                subtitle: $subtitle,
                type: 'tomorrow_event',
                order_id: $orderId
            );

        }

    }
}

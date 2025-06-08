<?php

namespace App\Console\Commands;

use App\Dtos\Fcm\FcmReceiverDto;
use App\Enums\UserType;
use App\Jobs\SendNotificationToAllCustomer;
use App\Models\Customer;
use App\Models\Event;
use Illuminate\Console\Command;

class NotifyCustomersToNewEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notify-customers-to-new-events';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify all customers about new events';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fcmReceivers = [];

        $customers  = Customer::all();

        foreach ($customers as $customer) {
            $fcmReceivers[] = FcmReceiverDto::make(
                id: $customer->id,
                type: UserType::Customer->value
            );
        }

        $notNotifiedEvents = $this->getNotNotifiedEvents();

        if (empty($notNotifiedEvents)) {
            return;
        }
        $title = 'New Event Available';
        $body = 'Check out the latest event available for you.';
        $subtitle = 'New Event Notification';

        foreach ($notNotifiedEvents as $event) {

            SendNotificationToAllCustomer::dispatchSync(
                receivers: $fcmReceivers,
                title: $title,
                message: $body,
                subtitle: $subtitle,
                type: 'new_event',
                event_id: $event->id
            );

        }

    }

    private function getNotNotifiedEvents()
    {

        $events = Event::query()
            ->whereDate('display_start_date', '<=', now())
            ->whereDate('display_end_date', '>=', now())
            ->where('active', 1)
            ->where('is_notified', false)
            ->get();
        if ($events->isEmpty()) {
            $this->info('No new events to notify customers about.');
            return [];
        }
        $this->info('Found ' . $events->count() . ' new events to notify customers about.');

        Event::whereIn('id', $events->pluck('id'))->update(['is_notified' => true]);
        return $events;


    }
}

<?php

namespace App\Jobs;

use App\Dtos\Fcm\FcmDto;
use App\Dtos\Fcm\FcmReceiverDto;
use App\Services\FcmService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNotificationToAllCustomer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private array|FcmReceiverDto $receivers,
        private string $title = '',
        private string $message = '',
        private string $subtitle = '',
        private string $type = '',
        private ?int $event_id = null,
        private ?int $order_id = null
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //

        try{
            FcmService::sendPushNotification(
                FcmDto::make(
                    receivers: $this->receivers,
                    title:  $this->title,
                    body: $this->message,
                    subtitle: $this->subtitle,
                    data: [
                        'type' => $this->type,
                        'event_id' => $this->event_id,
                        'order_id' => $this->order_id,
                    ]
                )
            );
        } catch (\Exception $e) {
            \Log::error('Error sending notification: ' . $e->getMessage());
        }

    }
}

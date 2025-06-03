<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmMessage;

class EventReminderNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    public function toFcm($notifiable)
    {
        return FcmMessage::create()
            ->setNotification(Notification::create()
                ->setTitle('Event Reminder!')
                ->setBody("Your event '{$this->event->title}' is happening tomorrow!")
            )
            ->setData(['event_id' => $this->event->id]);
    }

}

<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmMessage;

class CustomTextNotification extends Notification
{
    use Queueable;

    public function __construct(public string $title, public string $message) {}

    public function toFcm($notifiable)
    {
        return FcmMessage::create()
            ->setNotification(Notification::create()
                ->setTitle($this->title)
                ->setBody($this->message)
            );
    }

}

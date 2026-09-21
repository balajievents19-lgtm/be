<?php

namespace App\Notifications;

use App\Support\Brand;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerVerifyEmailChange extends Notification
{
    use Queueable;

    public function __construct(
        public string $verifyUrl,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Confirm your new email — '.Brand::NAME)
            ->line('Please confirm your new email address for your '.Brand::NAME.' account.')
            ->action('Confirm email', $this->verifyUrl)
            ->line('This link expires in 60 minutes. If you did not request an email change, ignore this message.');
    }
}

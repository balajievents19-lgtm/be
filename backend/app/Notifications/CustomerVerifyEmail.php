<?php

namespace App\Notifications;

use App\Support\Brand;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerVerifyEmail extends Notification
{
    use Queueable;

    public function __construct(
        public string $code,
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
        $minutes = (int) config('otp.expiry_minutes', 10);

        return (new MailMessage)
            ->subject('Verify your '.Brand::NAME.' email')
            ->line('Use this verification code to confirm your '.Brand::NAME.' account:')
            ->line($this->code)
            ->line('Or click the button below. This code expires in '.$minutes.' minutes.')
            ->action('Verify email', $this->verifyUrl)
            ->line('If you did not create this account, you can ignore this email.');
    }
}

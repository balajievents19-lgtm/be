<?php

namespace App\Notifications;

use App\Support\Brand;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerResetPassword extends Notification
{
    use Queueable;

    public function __construct(
        public string $token
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
        $frontend = rtrim((string) (config('seo.site_url') ?: env('SITE_URL', 'http://localhost:3000')), '/');
        $email = urlencode((string) $notifiable->email);
        $url = "{$frontend}/reset-password?token={$this->token}&email={$email}";

        return (new MailMessage)
            ->subject('Reset your '.Brand::NAME.' password')
            ->line('You are receiving this email because we received a password reset request for your customer account.')
            ->action('Reset Password', $url)
            ->line('This password reset link will expire in '.config('auth.passwords.customers.expire', 60).' minutes.')
            ->line('If you did not request a password reset, no further action is required.');
    }
}

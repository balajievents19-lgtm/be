<?php

namespace App\Notifications;

use App\Support\Brand;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerSecurityAlert extends Notification
{
    use Queueable;

    /**
     * @param  array<string, string>  $lines
     */
    public function __construct(
        public string $eventTitle,
        public array $lines = [],
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
        $mail = (new MailMessage)
            ->subject($this->eventTitle.' — '.Brand::NAME)
            ->line($this->eventTitle.'.');

        foreach ($this->lines as $line) {
            $mail->line($line);
        }

        $mail->line('If you did not make this change, reset your password and contact '.Brand::NAME.' immediately.');

        return $mail;
    }
}

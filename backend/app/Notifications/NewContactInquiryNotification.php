<?php

namespace App\Notifications;

use App\Models\ContactInquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewContactInquiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ContactInquiry $inquiry) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $inquiry = $this->inquiry;

        return (new MailMessage)
            ->subject('New enquiry: '.$inquiry->name)
            ->line('A new website enquiry was received.')
            ->line('Name: '.$inquiry->name)
            ->line('Phone: '.$inquiry->mobile)
            ->line('Email: '.($inquiry->email ?: '—'))
            ->line('Event type / service: '.($inquiry->service_interested ?: '—'))
            ->line('Event date: '.optional($inquiry->event_date)?->toDateString() ?: '—')
            ->line('Location: '.($inquiry->event_location ?: '—'))
            ->line('Budget: '.($inquiry->budget ?: '—'))
            ->line('Source: '.($inquiry->source ?: 'website'))
            ->line('Message:')
            ->line($inquiry->message)
            ->action('Open admin', url('/admin'));
    }
}

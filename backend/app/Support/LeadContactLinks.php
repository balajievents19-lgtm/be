<?php

namespace App\Support;

use App\Models\ContactInquiry;

final class LeadContactLinks
{
    public static function digits(?string $mobile): ?string
    {
        if (! filled($mobile)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $mobile);

        return filled($digits) ? $digits : null;
    }

    public static function callUrl(?string $mobile): ?string
    {
        if (! filled($mobile)) {
            return null;
        }

        $tel = preg_replace('/[^\d+]/', '', $mobile);

        return filled($tel) ? 'tel:'.$tel : null;
    }

    public static function whatsappUrl(ContactInquiry $lead): ?string
    {
        $digits = self::digits($lead->mobile);

        if ($digits === null) {
            return null;
        }

        $name = trim((string) $lead->name);
        $message = filled($name)
            ? "Hello {$name}, this is Balaji Events. We received your event enquiry. How can we help you?"
            : 'Hello, this is Balaji Events. We received your event enquiry. How can we help you?';

        return 'https://wa.me/'.$digits.'?text='.rawurlencode($message);
    }
}

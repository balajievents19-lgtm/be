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

        if (strlen($digits) === 10) {
            $digits = '91'.$digits;
        }

        $name = trim((string) $lead->name);
        $brand = Brand::name();
        $message = filled($name)
            ? "Hello {$name}, this is {$brand}. We received your event enquiry. How can we help you?"
            : "Hello, this is {$brand}. We received your event enquiry. How can we help you?";

        return 'https://wa.me/'.$digits.'?text='.rawurlencode($message);
    }
}

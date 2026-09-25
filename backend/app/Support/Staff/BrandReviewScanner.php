<?php

namespace App\Support\Staff;

use Illuminate\Support\Str;

final class BrandReviewScanner
{
    /**
     * Heuristic only — never treated as proof of a third-party logo.
     *
     * @param  array<string, mixed>  $payload
     */
    public static function mightContainExternalBranding(array $payload): bool
    {
        $haystack = Str::lower(implode(' ', array_filter([
            $payload['title'] ?? null,
            $payload['description'] ?? null,
            $payload['caption'] ?? null,
            $payload['alt_text'] ?? null,
            $payload['image'] ?? null,
            $payload['thumbnail'] ?? null,
            $payload['url'] ?? null,
            $payload['video_url'] ?? null,
        ], fn ($value) => is_string($value) && $value !== '')));

        if ($haystack === '') {
            return false;
        }

        foreach (['watermark', 'competitor-logo', 'third-party-logo', 'external-brand'] as $needle) {
            if (str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }
}

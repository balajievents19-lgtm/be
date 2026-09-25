<?php

namespace App\Support;

final class Brand
{
    public const NAME = 'Balaji Royal Events';

    public static function rewrite(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }

        $normalized = preg_replace('/\bBalaji Events\b/i', self::NAME, $text) ?? $text;
        $normalized = preg_replace('/\bBalaji Event\b/i', self::NAME, $normalized) ?? $normalized;
        $normalized = preg_replace('/\bBalajiEvents\b/i', self::NAME, $normalized) ?? $normalized;
        $normalized = preg_replace('/\bBalajiEvent\b/i', self::NAME, $normalized) ?? $normalized;

        return $normalized;
    }

    /**
     * Public brand label. Outdated “Balaji Event(s)” values are normalized.
     */
    public static function name(?string $companyName = null): string
    {
        $rewritten = trim((string) self::rewrite($companyName));

        return $rewritten !== '' ? $rewritten : self::NAME;
    }
}

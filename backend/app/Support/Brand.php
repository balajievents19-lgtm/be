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

        foreach (self::serviceAreaRewrites() as $from => $to) {
            $normalized = preg_replace($from, $to, $normalized) ?? $normalized;
        }

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

    /**
     * Public copy must not present Jaipur/Udaipur as primary service bases.
     *
     * @return array<string, string>
     */
    private static function serviceAreaRewrites(): array
    {
        return [
            '/Trusted across Jhunjhunu,\s*Jaipur and Udaipur/i' => 'Trusted across Jhunjhunu, Mandawa and Alsisar',
            '/Udaipur\s+\S+\s+Jaipur\s+\S+\s+Shekhawati/iu' => 'Jhunjhunu • Mandawa • Alsisar',
            '/Jaipur,\s*Udaipur and Shekhawati/i' => 'Jhunjhunu, Mandawa and Alsisar',
            '/across Jaipur,\s*Udaipur/i' => 'across Jhunjhunu, Mandawa',
            '/in Jaipur,\s*Udaipur/i' => 'in Jhunjhunu, Mandawa',
            '/Destination Wedding Udaipur/i' => 'Destination Wedding Rajasthan',
            '/Royal Wedding Jaipur/i' => 'Royal Wedding Rajasthan',
        ];
    }
}

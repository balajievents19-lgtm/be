<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Identifies trusted Nuxt SSR server-to-server read requests.
 * Never log the secret. Empty config disables trust (public limiter only).
 */
final class SsrInternalAuth
{
    public static function headerName(): string
    {
        return (string) config('ssr.header', 'X-SSR-Secret');
    }

    public static function configuredSecret(): string
    {
        return trim((string) config('ssr.internal_secret', ''));
    }

    public static function isConfigured(): bool
    {
        return self::configuredSecret() !== '';
    }

    /**
     * True when the request carries a valid SSR secret.
     * Callers must also enforce safe HTTP methods before raising limits.
     */
    public static function check(Request $request): bool
    {
        $expected = self::configuredSecret();
        if ($expected === '') {
            return false;
        }

        $provided = trim((string) $request->header(self::headerName(), ''));
        if ($provided === '') {
            return false;
        }

        return hash_equals($expected, $provided);
    }

    /**
     * Trusted SSR read traffic: configured secret + idempotent method.
     */
    public static function isTrustedRead(Request $request): bool
    {
        return $request->isMethodSafe() && self::check($request);
    }
}

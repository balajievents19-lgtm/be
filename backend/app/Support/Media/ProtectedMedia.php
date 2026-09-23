<?php

namespace App\Support\Media;

/**
 * Opaque, deterministic display URLs for CMS files.
 * The storage path is encrypted and MAC'd so it never appears in the URL.
 */
final class ProtectedMedia
{
    public const URL_PREFIX = '/protected-media/';

    public static function displayUrl(string $relativePath): ?string
    {
        $normalized = self::normalizeRelative($relativePath);
        if ($normalized === null) {
            return null;
        }

        return self::URL_PREFIX.self::token($normalized);
    }

    public static function token(string $relativePath): string
    {
        $key = self::secret();
        $iv = substr(hash_hmac('sha256', 'iv|'.$relativePath, $key, true), 0, 16);
        $ciphertext = openssl_encrypt($relativePath, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        $mac = hash_hmac('sha256', $iv.$ciphertext, $key, true);

        return rtrim(strtr(base64_encode($mac.$iv.$ciphertext), '+/', '-_'), '=');
    }

    public static function pathFromToken(string $token): ?string
    {
        $token = trim($token);
        if ($token === '' || strlen($token) > 4096) {
            return null;
        }

        $b64 = strtr($token, '-_', '+/');
        $pad = strlen($b64) % 4;
        if ($pad !== 0) {
            $b64 .= str_repeat('=', 4 - $pad);
        }

        $raw = base64_decode($b64, true);
        if (! is_string($raw) || strlen($raw) < 49) {
            return null;
        }

        $mac = substr($raw, 0, 32);
        $iv = substr($raw, 32, 16);
        $ciphertext = substr($raw, 48);
        $key = self::secret();
        $expected = hash_hmac('sha256', $iv.$ciphertext, $key, true);
        if (! hash_equals($expected, $mac)) {
            return null;
        }

        $path = openssl_decrypt($ciphertext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        if (! is_string($path)) {
            return null;
        }

        return self::normalizeRelative($path);
    }

    public static function normalizeRelative(string $path): ?string
    {
        $path = str_replace('\\', '/', trim($path));
        $path = ltrim($path, '/');

        if ($path === '' || str_contains($path, '..') || str_contains($path, "\0")) {
            return null;
        }

        return $path;
    }

    private static function secret(): string
    {
        return hash('sha256', (string) config('app.key'), true);
    }
}

<?php

namespace App\Support\Media;

class PublicStorageUrl
{
    /**
     * Same-site CMS files as origin-relative /storage/... paths.
     * External URLs (YouTube, Instagram, CDNs) are returned unchanged.
     */
    public static function make(?string $path): ?string
    {
        if (! is_string($path)) {
            return null;
        }

        $value = trim($path);
        if ($value === '') {
            return null;
        }

        if (str_starts_with($value, '//')) {
            $parsed = parse_url('https:'.$value);
            $pathname = is_array($parsed) ? ($parsed['path'] ?? '') : '';
            if (is_string($pathname) && str_starts_with($pathname, '/storage/')) {
                return self::storagePathWithQuery($parsed);
            }

            return $value;
        }

        if (preg_match('#^https?://#i', $value) === 1) {
            $parsed = parse_url($value);
            $pathname = is_array($parsed) ? ($parsed['path'] ?? '') : '';
            if (is_string($pathname) && str_starts_with($pathname, '/storage/')) {
                return self::storagePathWithQuery($parsed);
            }

            return $value;
        }

        if (str_starts_with($value, '/storage/')) {
            return $value;
        }

        if (str_starts_with($value, '/')) {
            return $value;
        }

        return '/storage/'.ltrim(str_replace('\\', '/', $value), '/');
    }

    /**
     * @param  array<string, mixed>|false  $parsed
     */
    private static function storagePathWithQuery(array|false $parsed): string
    {
        if (! is_array($parsed)) {
            return '/storage/';
        }

        $path = (string) ($parsed['path'] ?? '/storage/');
        $query = isset($parsed['query']) && $parsed['query'] !== ''
            ? '?'.$parsed['query']
            : '';

        return $path.$query;
    }
}

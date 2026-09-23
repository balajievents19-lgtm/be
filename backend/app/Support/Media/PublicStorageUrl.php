<?php

namespace App\Support\Media;

class PublicStorageUrl
{
    /**
     * Same-site CMS files as origin-relative protected display URLs.
     * External URLs (YouTube, Instagram, CDNs) and static /images chrome are unchanged.
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

        if (str_starts_with($value, ProtectedMedia::URL_PREFIX)) {
            return $value;
        }

        [$relative, $query] = self::cmsRelativeAndQuery($value);
        if ($relative === null) {
            return $value;
        }

        $url = ProtectedMedia::displayUrl($relative);
        if ($url === null) {
            return $value;
        }

        return $query !== '' ? $url.'?'.$query : $url;
    }

    /**
     * @return array{0: ?string, 1: string}
     */
    private static function cmsRelativeAndQuery(string $value): array
    {
        if (str_starts_with($value, '//')) {
            return self::fromParsed(parse_url('https:'.$value));
        }

        if (preg_match('#^https?://#i', $value) === 1) {
            return self::fromParsed(parse_url($value));
        }

        if (str_starts_with($value, '/storage/')) {
            $parsed = parse_url($value);

            return self::fromParsed(is_array($parsed) ? $parsed : false);
        }

        if (str_starts_with($value, '/')) {
            return [null, ''];
        }

        $parsed = parse_url('/'.$value);
        if (! is_array($parsed)) {
            return [ProtectedMedia::normalizeRelative($value), ''];
        }

        $relative = ProtectedMedia::normalizeRelative(ltrim((string) ($parsed['path'] ?? ''), '/'));
        $query = isset($parsed['query']) && is_string($parsed['query']) ? $parsed['query'] : '';

        return [$relative, $query];
    }

    /**
     * @param  array<string, mixed>|false  $parsed
     * @return array{0: ?string, 1: string}
     */
    private static function fromParsed(array|false $parsed): array
    {
        if (! is_array($parsed)) {
            return [null, ''];
        }

        $pathname = (string) ($parsed['path'] ?? '');
        if (! str_starts_with($pathname, '/storage/')) {
            return [null, ''];
        }

        $relative = ProtectedMedia::normalizeRelative(substr($pathname, strlen('/storage/')));
        $query = isset($parsed['query']) && is_string($parsed['query']) ? $parsed['query'] : '';

        return [$relative, $query];
    }
}

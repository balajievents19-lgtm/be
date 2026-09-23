<?php

namespace App\Services\Media;

use Illuminate\Support\Str;

/**
 * Validate external media URLs and resolve safe embed/open metadata.
 * Never stores video files — URL + metadata only.
 */
final class ExternalMediaUrl
{
    public const PROVIDERS = [
        'youtube',
        'instagram',
        'facebook',
        'google_drive',
        'vimeo',
        'other',
    ];

    public const MEDIA_TYPES = [
        'video',
        'social_post',
        'external',
    ];

    /**
     * @return array{valid: bool, provider: string, embed_url: string|null, open_url: string, mode: string, message: string|null}
     */
    public static function resolve(string $url, ?string $preferredProvider = null): array
    {
        $url = trim($url);
        $openUrl = $url;
        $invalid = [
            'valid' => false,
            'provider' => $preferredProvider ?: 'other',
            'embed_url' => null,
            'open_url' => $openUrl,
            'mode' => 'link',
            'message' => 'Invalid or unsupported URL.',
        ];

        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return $invalid;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        if (! in_array($scheme, ['http', 'https'], true)) {
            $invalid['message'] = 'Only http/https URLs are allowed.';

            return $invalid;
        }

        if (Str::startsWith(strtolower($url), 'javascript:')) {
            return $invalid;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $provider = $preferredProvider && in_array($preferredProvider, self::PROVIDERS, true)
            ? $preferredProvider
            : self::detectProvider($host, $url);

        return match ($provider) {
            'youtube' => self::resolveYouTube($url),
            'vimeo' => self::resolveVimeo($url, $openUrl),
            'instagram' => self::resolveLinkOnly($url, 'instagram', 'View on Instagram'),
            'facebook' => self::resolveLinkOnly($url, 'facebook', 'View on Facebook'),
            'google_drive' => self::resolveLinkOnly($url, 'google_drive', 'Open in Google Drive'),
            default => self::resolveLinkOnly($url, 'other', 'Open link'),
        };
    }

    public static function detectProvider(string $host, string $url): string
    {
        if (str_contains($host, 'youtube.com') || $host === 'youtu.be' || str_contains($host, 'youtube-nocookie.com')) {
            return 'youtube';
        }
        if (str_contains($host, 'vimeo.com') || str_contains($host, 'player.vimeo.com')) {
            return 'vimeo';
        }
        if (str_contains($host, 'instagram.com')) {
            return 'instagram';
        }
        if (str_contains($host, 'facebook.com') || str_contains($host, 'fb.watch') || str_contains($host, 'fb.com')) {
            return 'facebook';
        }
        if (str_contains($host, 'drive.google.com') || str_contains($host, 'docs.google.com')) {
            return 'google_drive';
        }

        return 'other';
    }

    public static function youtubeId(string $url): ?string
    {
        $url = trim($url);
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = (string) parse_url($url, PHP_URL_PATH);
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        $id = null;
        if ($host === 'youtu.be') {
            $id = explode('/', trim($path, '/'))[0] ?? null;
        } elseif (isset($query['v']) && is_string($query['v'])) {
            $id = $query['v'];
        } elseif (preg_match('#/(embed|shorts|live)/([A-Za-z0-9_-]{6,})#', $path, $m)) {
            $id = $m[2];
        }

        if (! is_string($id) || preg_match('/^[A-Za-z0-9_-]{6,}$/', $id) !== 1) {
            return null;
        }

        return $id;
    }

    /**
     * @return array{valid: bool, provider: string, embed_url: string|null, open_url: string, mode: string, message: string|null}
     */
    private static function resolveYouTube(string $url): array
    {
        $id = self::youtubeId($url);
        if ($id === null) {
            return [
                'valid' => false,
                'provider' => 'youtube',
                'embed_url' => null,
                'open_url' => $url,
                'mode' => 'link',
                'message' => 'Enter a valid YouTube watch, youtu.be, Shorts, or embed URL.',
            ];
        }

        return [
            'valid' => true,
            'provider' => 'youtube',
            'embed_url' => 'https://www.youtube-nocookie.com/embed/'.$id,
            'open_url' => 'https://www.youtube.com/watch?v='.$id,
            'mode' => 'embed',
            'message' => null,
        ];
    }

    /**
     * @return array{valid: bool, provider: string, embed_url: string|null, open_url: string, mode: string, message: string|null}
     */
    private static function resolveVimeo(string $url, string $openUrl): array
    {
        $path = (string) parse_url($url, PHP_URL_PATH);
        if (! preg_match('#/(\d{6,})#', $path, $m)) {
            return [
                'valid' => true,
                'provider' => 'vimeo',
                'embed_url' => null,
                'open_url' => $openUrl,
                'mode' => 'link',
                'message' => 'Could not build a safe Vimeo embed; open the link instead.',
            ];
        }

        $id = $m[1];

        return [
            'valid' => true,
            'provider' => 'vimeo',
            'embed_url' => 'https://player.vimeo.com/video/'.$id,
            'open_url' => 'https://vimeo.com/'.$id,
            'mode' => 'embed',
            'message' => null,
        ];
    }

    /**
     * @return array{valid: bool, provider: string, embed_url: string|null, open_url: string, mode: string, message: string|null}
     */
    private static function resolveLinkOnly(string $url, string $provider, string $label): array
    {
        return [
            'valid' => true,
            'provider' => $provider,
            'embed_url' => null,
            'open_url' => $url,
            'mode' => 'link',
            'message' => $label,
        ];
    }
}

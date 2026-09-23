<?php

namespace App\Support\Media;

use App\Enums\GalleryVideoSource;
use App\Services\Media\ExternalMediaUrl;

/**
 * Safe gallery video metadata. Stores source + URL only — never downloads video files.
 */
final class GalleryVideoEmbed
{
    /**
     * @return array{
     *     valid: bool,
     *     source: string,
     *     video_id: string|null,
     *     embed_url: string|null,
     *     open_url: string|null,
     *     mode: string,
     *     poster_url: string|null,
     *     cta_label: string|null,
     *     message: string|null
     * }
     */
    public static function resolve(?string $source, ?string $url): array
    {
        $url = is_string($url) ? trim($url) : '';
        $source = is_string($source) ? strtolower(trim($source)) : '';
        $empty = [
            'valid' => false,
            'source' => $source !== '' ? $source : 'other',
            'video_id' => null,
            'embed_url' => null,
            'open_url' => null,
            'mode' => 'link',
            'poster_url' => null,
            'cta_label' => null,
            'message' => 'A valid HTTPS video URL is required.',
        ];

        if ($url === '' || $source === '') {
            return $empty;
        }

        if (! self::sourceMatches($source, $url)) {
            $empty['message'] = 'The URL does not match the selected video source.';

            return $empty;
        }

        return match ($source) {
            GalleryVideoSource::Youtube->value => self::youtube($url),
            GalleryVideoSource::Instagram->value => self::instagram($url),
            GalleryVideoSource::Facebook->value => self::facebook($url),
            default => self::other($url),
        };
    }

    public static function sourceMatches(string $source, string $url): bool
    {
        $url = trim($url);
        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        if ($scheme !== 'https' && ! ($source === GalleryVideoSource::Youtube->value && $scheme === 'http')) {
            return false;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $detected = ExternalMediaUrl::detectProvider($host, $url);

        return match ($source) {
            GalleryVideoSource::Youtube->value => ExternalMediaUrl::youtubeId($url) !== null,
            GalleryVideoSource::Instagram->value => $detected === 'instagram',
            GalleryVideoSource::Facebook->value => $detected === 'facebook',
            GalleryVideoSource::Other->value => $scheme === 'https',
            default => false,
        };
    }

    /**
     * @return array{valid: bool, source: string, video_id: string|null, embed_url: string|null, open_url: string|null, mode: string, poster_url: string|null, cta_label: string|null, message: string|null}
     */
    private static function youtube(string $url): array
    {
        $id = ExternalMediaUrl::youtubeId($url);
        if ($id === null) {
            return [
                'valid' => false,
                'source' => 'youtube',
                'video_id' => null,
                'embed_url' => null,
                'open_url' => null,
                'mode' => 'link',
                'poster_url' => null,
                'cta_label' => null,
                'message' => 'Enter a valid YouTube watch, youtu.be, Shorts, or embed URL.',
            ];
        }

        return [
            'valid' => true,
            'source' => 'youtube',
            'video_id' => $id,
            'embed_url' => 'https://www.youtube-nocookie.com/embed/'.$id,
            'open_url' => null,
            'mode' => 'embed',
            'poster_url' => 'https://i.ytimg.com/vi/'.$id.'/hqdefault.jpg',
            'cta_label' => null,
            'message' => null,
        ];
    }

    /**
     * @return array{valid: bool, source: string, embed_url: string|null, open_url: string|null, mode: string, poster_url: string|null, cta_label: string|null, message: string|null}
     */
    private static function instagram(string $url): array
    {
        $path = (string) parse_url($url, PHP_URL_PATH);
        if (! preg_match('#/(reel|p|tv)/([A-Za-z0-9_-]+)#i', $path, $m)) {
            return [
                'valid' => true,
                'source' => 'instagram',
                'video_id' => null,
                'embed_url' => null,
                'open_url' => $url,
                'mode' => 'link',
                'poster_url' => null,
                'cta_label' => 'View on Instagram',
                'message' => 'Open this Instagram post on Instagram.',
            ];
        }

        $kind = strtolower($m[1]);
        $code = $m[2];

        return [
            'valid' => true,
            'source' => 'instagram',
            'video_id' => null,
            'embed_url' => 'https://www.instagram.com/'.$kind.'/'.$code.'/embed/',
            'open_url' => 'https://www.instagram.com/'.$kind.'/'.$code.'/',
            'mode' => 'embed',
            'poster_url' => null,
            'cta_label' => null,
            'message' => null,
        ];
    }

    /**
     * @return array{valid: bool, source: string, embed_url: string|null, open_url: string|null, mode: string, poster_url: string|null, cta_label: string|null, message: string|null}
     */
    private static function facebook(string $url): array
    {
        return [
            'valid' => true,
            'source' => 'facebook',
            'video_id' => null,
            'embed_url' => 'https://www.facebook.com/plugins/video.php?href='.rawurlencode($url).'&show_text=0',
            'open_url' => $url,
            'mode' => 'embed',
            'poster_url' => null,
            'cta_label' => null,
            'message' => null,
        ];
    }

    /**
     * @return array{valid: bool, source: string, embed_url: string|null, open_url: string|null, mode: string, poster_url: string|null, cta_label: string|null, message: string|null}
     */
    private static function other(string $url): array
    {
        $resolved = ExternalMediaUrl::resolve($url);
        $mode = $resolved['mode'];
        $embed = $resolved['embed_url'];
        $youtubeId = ExternalMediaUrl::youtubeId($url);
        if ($youtubeId !== null) {
            return [
                'valid' => true,
                'source' => 'other',
                'video_id' => $youtubeId,
                'embed_url' => 'https://www.youtube-nocookie.com/embed/'.$youtubeId,
                'open_url' => null,
                'mode' => 'embed',
                'poster_url' => 'https://i.ytimg.com/vi/'.$youtubeId.'/hqdefault.jpg',
                'cta_label' => null,
                'message' => null,
            ];
        }
        if ($mode === 'embed' && ! self::isAllowedOtherEmbed($embed)) {
            $mode = 'link';
            $embed = null;
        }

        return [
            'valid' => (bool) $resolved['valid'],
            'source' => 'other',
            'video_id' => null,
            'embed_url' => $embed,
            'open_url' => $resolved['open_url'] ?: $url,
            'mode' => $mode,
            'poster_url' => null,
            'cta_label' => $mode === 'link' ? ($resolved['message'] ?: 'Open video') : null,
            'message' => $mode === 'link' ? ($resolved['message'] ?: 'This link cannot be embedded safely.') : null,
        ];
    }

    private static function isAllowedOtherEmbed(?string $embed): bool
    {
        if (! is_string($embed) || $embed === '') {
            return false;
        }

        $host = strtolower((string) parse_url($embed, PHP_URL_HOST));

        return in_array($host, [
            'www.youtube-nocookie.com',
            'www.youtube.com',
            'player.vimeo.com',
        ], true);
    }
}

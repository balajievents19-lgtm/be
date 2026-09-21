<?php

namespace App\Support;

use App\Services\Seo\SeoService;
use Illuminate\Support\Facades\Cache;

class ContentCache
{
    public const TTL = 300;

    public const HOME = 'api.content.home';

    public const SETTINGS = 'api.content.settings';

    public const SERVICES = 'api.content.services';

    public const GALLERY = 'api.content.gallery';

    public const GALLERY_CATEGORIES = 'api.content.gallery_categories';

    public const BLOG = 'api.content.blog';

    public const FAQS = 'api.content.faqs';

    public const HERO = 'api.content.hero';

    public const NAVIGATION_HEADER = 'api.content.navigation.header';

    public const NAVIGATION_FOOTER = 'api.content.navigation.footer';

    public const EVENT_OVERVIEWS = 'api.content.event_overviews';

    public const TESTIMONIALS = 'api.content.testimonials';

    public const EXTERNAL_MEDIA = 'api.content.external_media';

    public const EVENT_TYPES = 'api.content.event_types';

    public const TEAM = 'api.content.team';

    public const STATISTICS = 'api.content.statistics';

    public const CTA = 'api.content.cta';

    public const OFFICES = 'api.content.offices';

    public const CATEGORIES = 'api.content.service_categories';

    public const PACKAGES = 'api.content.service_packages';

    public const REDIRECTS = 'api.content.redirects';

    public const SEO_HOME = 'api.content.seo.home';

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public static function remember(string $key, callable $callback): mixed
    {
        return Cache::remember($key, self::TTL, $callback);
    }

    /**
     * Cache a detail payload. Epoch bumps invalidate all show keys for the module.
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public static function rememberShow(string $module, string $slug, callable $callback): mixed
    {
        $epoch = (int) Cache::get(self::showEpochKey($module), 0);

        return Cache::remember(
            'api.content.'.$module.'.show.'.$epoch.'.'.$slug,
            self::TTL,
            $callback
        );
    }

    public static function bumpShowEpoch(string $module): void
    {
        $key = self::showEpochKey($module);
        Cache::forever($key, ((int) Cache::get($key, 0)) + 1);
    }

    private static function showEpochKey(string $module): string
    {
        return 'api.content.'.$module.'.show.epoch';
    }

    public static function flushAll(): void
    {
        foreach ([
            self::HOME,
            self::SETTINGS,
            self::SERVICES,
            self::GALLERY,
            self::GALLERY_CATEGORIES,
            self::BLOG,
            self::FAQS,
            self::HERO,
            self::NAVIGATION_HEADER,
            self::NAVIGATION_FOOTER,
            self::EVENT_OVERVIEWS,
            self::TESTIMONIALS,
            self::EXTERNAL_MEDIA,
            self::EVENT_TYPES,
            self::TEAM,
            self::STATISTICS,
            self::CTA,
            self::OFFICES,
            self::CATEGORIES,
            self::PACKAGES,
            self::REDIRECTS,
            self::SEO_HOME,
        ] as $key) {
            Cache::forget($key);
        }

        foreach (['services', 'gallery', 'gallery-category', 'blog', 'faqs'] as $module) {
            self::bumpShowEpoch($module);
        }

        app(SeoService::class)->clearCache();
    }

    /**
     * Flush homepage + module keys that affect public responses.
     *
     * @param  list<string>  $keys
     */
    public static function flush(string ...$keys): void
    {
        Cache::forget(self::HOME);
        Cache::forget(self::SEO_HOME);

        foreach ($keys as $key) {
            Cache::forget($key);

            if ($key === self::EXTERNAL_MEDIA) {
                Cache::forget(self::EXTERNAL_MEDIA.'.homepage');
            }

            $module = match ($key) {
                self::SERVICES => 'services',
                self::GALLERY,
                self::GALLERY_CATEGORIES => 'gallery',
                self::BLOG => 'blog',
                self::FAQS => 'faqs',
                self::NAVIGATION_HEADER,
                self::NAVIGATION_FOOTER => 'navigation',
                default => null,
            };

            if ($module !== null) {
                self::bumpShowEpoch($module);
                if ($module === 'gallery') {
                    self::bumpShowEpoch('gallery-category');
                }
            }
        }

        app(SeoService::class)->clearCache();
    }
}

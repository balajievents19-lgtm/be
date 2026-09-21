<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Public website URL (frontend origin)
    |--------------------------------------------------------------------------
    | REQUIRED in production for correct SEO hosts.
    |
    | Used for: canonical URLs, Open Graph url, JSON-LD @id/url, sitemap <loc>.
    | Set SITE_URL to the public Nuxt origin (not the Laravel/API host).
    |
    | Precedence (see SeoService::siteUrl):
    |   1. SITE_URL (or FRONTEND_URL if SITE_URL empty)
    |   2. Admin / settings canonical_url
    |   3. APP_URL (last resort — usually wrong for public SEO)
    |
    | After changing SITE_URL: php artisan config:clear && php artisan cache:clear
    | then php artisan optimize (see PRODUCTION_RELEASE.md / DEPLOYMENT.md).
    */
    'site_url' => env('SITE_URL', env('FRONTEND_URL', null)),

    'sitemap_cache_ttl' => (int) env('SEO_SITEMAP_CACHE_TTL', 3600),

    'schema_cache_ttl' => (int) env('SEO_SCHEMA_CACHE_TTL', 3600),

    'default_robots' => 'index, follow',

    'twitter_card' => 'summary_large_image',

    /*
    |--------------------------------------------------------------------------
    | Static marketing pages included in the sitemap
    |--------------------------------------------------------------------------
    */
    'static_pages' => [
        ['path' => '/', 'changefreq' => 'daily', 'priority' => '1.0'],
        ['path' => '/about', 'changefreq' => 'monthly', 'priority' => '0.8'],
        ['path' => '/services', 'changefreq' => 'weekly', 'priority' => '0.9'],
        ['path' => '/gallery', 'changefreq' => 'weekly', 'priority' => '0.8'],
        ['path' => '/packages', 'changefreq' => 'weekly', 'priority' => '0.8'],
        ['path' => '/blog', 'changefreq' => 'daily', 'priority' => '0.9'],
        ['path' => '/faq', 'changefreq' => 'monthly', 'priority' => '0.7'],
        ['path' => '/contact', 'changefreq' => 'monthly', 'priority' => '0.8'],
        ['path' => '/events', 'changefreq' => 'weekly', 'priority' => '0.8'],
        ['path' => '/media', 'changefreq' => 'weekly', 'priority' => '0.6'],
        ['path' => '/privacy-policy', 'changefreq' => 'yearly', 'priority' => '0.3'],
        ['path' => '/terms', 'changefreq' => 'yearly', 'priority' => '0.3'],
    ],
];

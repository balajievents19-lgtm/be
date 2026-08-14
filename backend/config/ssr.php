<?php

/**
 * Trusted Nuxt SSR → Laravel read-API identity.
 *
 * Public anonymous clients stay on throttle:api (60/min/IP).
 * Valid X-SSR-Secret on safe (GET/HEAD) methods uses a separate finite limiter
 * so concurrent SSR does not exhaust the public quota.
 * POST /api/contact and POST /api/newsletter keep their own throttles unchanged.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | SSR internal shared secret
    |--------------------------------------------------------------------------
    |
    | Must match Nuxt server-only runtimeConfig (NUXT_SSR_INTERNAL_SECRET).
    | Empty = no trusted SSR identity (all requests use the public limiter).
    |
    */
    'internal_secret' => env('SSR_INTERNAL_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Request header name
    |--------------------------------------------------------------------------
    */
    'header' => 'X-SSR-Secret',

    /*
    |--------------------------------------------------------------------------
    | Trusted SSR read rate limit (per minute, per IP)
    |--------------------------------------------------------------------------
    |
    | Finite on purpose — not unlimited. Sized for concurrent Nuxt SSR
    | fan-out (settings + navigation + SEO + page content), not public abuse.
    |
    */
    'rate_limit_per_minute' => (int) env('SSR_INTERNAL_RATE_LIMIT', 300),

];

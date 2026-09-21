<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Customer OAuth (Socialite) — credentials from env only
    |--------------------------------------------------------------------------
    */
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env(
            'GOOGLE_REDIRECT_URI',
            rtrim((string) env('APP_URL', 'http://localhost:8000'), '/').'/api/customer/oauth/google/callback'
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Places (Business reviews on the website)
    |--------------------------------------------------------------------------
    | Server-only. Never expose GOOGLE_PLACES_API_KEY to Nuxt public runtime.
    */
    'google_places' => [
        'api_key' => env('GOOGLE_PLACES_API_KEY'),
        'place_id' => env('GOOGLE_PLACE_ID'),
        'reviews_url' => env('GOOGLE_REVIEWS_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Business Profile (review replies — optional, OAuth)
    |--------------------------------------------------------------------------
    | Replies stay disabled until these are set and the OAuth flow is completed.
    */
    'google_business_profile' => [
        'client_id' => env('GOOGLE_GBP_CLIENT_ID'),
        'client_secret' => env('GOOGLE_GBP_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_GBP_REDIRECT_URI'),
        'account_id' => env('GOOGLE_GBP_ACCOUNT_ID'),
        'location_id' => env('GOOGLE_GBP_LOCATION_ID'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env(
            'FACEBOOK_REDIRECT_URI',
            rtrim((string) env('APP_URL', 'http://localhost:8000'), '/').'/api/customer/oauth/facebook/callback'
        ),
    ],

    /*
    | Instagram consumer login is not implemented.
    | Meta does not currently offer a supported general customer-login flow
    | equivalent to Google/Facebook Login (Business/Creator APIs are not used here).
    */
    'instagram' => [
        'client_id' => env('INSTAGRAM_CLIENT_ID'),
        'client_secret' => env('INSTAGRAM_CLIENT_SECRET'),
        'redirect' => env('INSTAGRAM_REDIRECT_URI'),
    ],

];

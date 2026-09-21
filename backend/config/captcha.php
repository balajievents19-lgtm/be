<?php

return [
    /*
    | Provider-ready abuse protection. Disabled until CAPTCHA_ENABLED=true
    | and a secret is configured. Local development stays usable without CAPTCHA.
    */
    'enabled' => (bool) env('CAPTCHA_ENABLED', false),
    'provider' => env('CAPTCHA_PROVIDER', 'turnstile'),
    'secret' => env('CAPTCHA_SECRET'),
    'site_key' => env('CAPTCHA_SITE_KEY'),
];

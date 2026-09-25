<?php

/**
 * Email verification OTP (not SMS).
 * A real SMS provider is not wired; do not invent OTPs or treat email OTP as SMS.
 */
return [
    'expiry_minutes' => (int) env('OTP_EXPIRY_MINUTES', 10),
    'max_attempts' => (int) env('OTP_MAX_ATTEMPTS', 5),
    'resend_seconds' => (int) env('OTP_RESEND_SECONDS', 60),
    'length' => 6,
];

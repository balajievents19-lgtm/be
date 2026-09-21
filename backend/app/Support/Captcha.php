<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

final class Captcha
{
    public static function assertValid(Request $request): void
    {
        if (! config('captcha.enabled')) {
            return;
        }

        $secret = trim((string) config('captcha.secret'));
        if ($secret === '') {
            return;
        }

        $token = (string) $request->input('captcha_token', '');
        if ($token === '') {
            throw ValidationException::withMessages([
                'captcha_token' => ['Please complete the security check.'],
            ]);
        }

        $ok = Http::asForm()->timeout(8)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => $secret,
            'response' => $token,
            'remoteip' => $request->ip(),
        ])->json('success');

        if ($ok !== true) {
            throw ValidationException::withMessages([
                'captcha_token' => ['Security check failed. Please try again.'],
            ]);
        }
    }
}

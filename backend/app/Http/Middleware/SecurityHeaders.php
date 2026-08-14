<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Minimal production security headers.
 * Intentionally avoids CSP so Nuxt, Filament, Livewire, fonts, maps, and WhatsApp keep working.
 */
class SecurityHeaders
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff', false);
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN', false);
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin', false);
        $response->headers->set('X-XSS-Protection', '0', false);
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=()',
            false
        );

        return $response;
    }
}

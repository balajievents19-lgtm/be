<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Filament FilePond ("Waiting for size") loads Livewire preview URLs as fetch/XHR.
 * Absolute APP_URL hosts (www vs apex) make those previews cross-origin and they hang.
 */
class AlignAdminAssetOrigin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isAdminAssetRequest($request)) {
            URL::forceRootUrl($request->getSchemeAndHttpHost());
            URL::forceScheme($request->getScheme());
        }

        return $next($request);
    }

    private function isAdminAssetRequest(Request $request): bool
    {
        return $request->is('admin', 'admin/*', 'livewire', 'livewire/*');
    }
}

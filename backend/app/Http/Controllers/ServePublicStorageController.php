<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Media\ProtectedMedia;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Original CMS files under /storage/{path}. Guests and customers are denied.
 * Filament/admin previews keep working with an authenticated web session.
 */
class ServePublicStorageController extends Controller
{
    public function __invoke(Request $request, string $path): Response
    {
        $relative = ProtectedMedia::normalizeRelative($path);
        abort_if($relative === null, 404);

        $user = $request->user('web') ?? $request->user();
        if (
            $user === null
            || ! $user instanceof User
            || ! $user->canAccessPanel(Filament::getPanel('admin'))
        ) {
            abort(403);
        }

        $disk = Storage::disk('public');
        abort_if(! $disk->exists($relative), 404);

        $mime = $disk->mimeType($relative) ?: 'application/octet-stream';
        $contents = $disk->get($relative);
        abort_if($contents === null, 404);

        return response($contents, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}

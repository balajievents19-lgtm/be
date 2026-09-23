<?php

namespace App\Http\Controllers;

use App\Support\Media\DisplayImageFactory;
use App\Support\Media\ProtectedMedia;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class MediaDisplayController extends Controller
{
    public function __invoke(string $token, DisplayImageFactory $factory): Response
    {
        $path = ProtectedMedia::pathFromToken($token);
        abort_if($path === null, 404);

        $disk = Storage::disk('public');
        abort_if(! $disk->exists($path), 404);

        $rendered = $factory->make($disk->get($path), $path);

        return response($rendered['contents'], 200, [
            'Content-Type' => $rendered['mime'],
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'public, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}

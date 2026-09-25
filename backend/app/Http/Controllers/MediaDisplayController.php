<?php

namespace App\Http\Controllers;

use App\Support\Media\DisplayImageFactory;
use App\Support\Media\ProcessedMediaCache;
use App\Support\Media\ProtectedMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class MediaDisplayController extends Controller
{
    public function __invoke(
        Request $request,
        string $token,
        DisplayImageFactory $factory,
        ProcessedMediaCache $cache,
    ): Response {
        $path = ProtectedMedia::pathFromToken($token);
        abort_if($path === null, 404);

        $disk = Storage::disk('public');
        abort_if(! $disk->exists($path), 404);

        $sourceMime = null;
        try {
            $sourceMime = $disk->mimeType($path);
        } catch (\Throwable) {
            $sourceMime = null;
        }

        $format = $factory->negotiateDisplayFormat($request->header('Accept'), $path, $sourceMime);
        $etag = $cache->etag($disk, $path, DisplayImageFactory::MODE_DISPLAY, $format);
        $mtime = $disk->lastModified($path);

        $notModified = new Response();
        $notModified->setPublic();
        $notModified->setMaxAge(86400);
        $notModified->headers->addCacheControlDirective('must-revalidate');
        $notModified->setEtag($etag);
        $notModified->setLastModified((new \DateTimeImmutable())->setTimestamp($mtime));
        $notModified->headers->set('X-Content-Type-Options', 'nosniff');
        $notModified->headers->set('Vary', 'Accept');
        if ($notModified->isNotModified($request)) {
            return $notModified;
        }

        $rendered = $cache->remember(
            $disk,
            $path,
            DisplayImageFactory::MODE_DISPLAY,
            $format,
            fn (): array => $factory->make($disk->get($path), $path, DisplayImageFactory::MODE_DISPLAY, $format)
        );

        return response($rendered['contents'], 200, [
            'Content-Type' => $rendered['mime'],
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'public, max-age=86400, must-revalidate',
            'ETag' => '"'.$rendered['etag'].'"',
            'Last-Modified' => gmdate('D, d M Y H:i:s', $rendered['last_modified']).' GMT',
            'X-Content-Type-Options' => 'nosniff',
            'X-Media-Cache' => $rendered['hit'] ? 'HIT' : 'MISS',
            'Vary' => 'Accept',
        ]);
    }
}

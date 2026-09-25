<?php

namespace App\Support\Media;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

/**
 * Disk cache of watermarked display/download derivatives.
 * Originals stay on their source disk and are never overwritten.
 */
final class ProcessedMediaCache
{
    public const VERSION = 'wm3x3-opt-v2';

    /**
     * @param  callable(): array{contents: string, mime: string}  $producer
     * @return array{contents: string, mime: string, etag: string, last_modified: int, hit: bool}
     */
    public function remember(Filesystem $sourceDisk, string $relativePath, string $mode, string $format, callable $producer): array
    {
        $etag = $this->etag($sourceDisk, $relativePath, $mode, $format);
        $payloadPath = $this->payloadPath($etag);
        $metaPath = $this->metaPath($etag);
        $cache = Storage::disk('local');

        if ($cache->exists($payloadPath) && $cache->exists($metaPath)) {
            $meta = json_decode((string) $cache->get($metaPath), true);
            $contents = $cache->get($payloadPath);
            if (is_string($contents) && $contents !== '' && is_array($meta) && isset($meta['mime'])) {
                return [
                    'contents' => $contents,
                    'mime' => (string) $meta['mime'],
                    'etag' => $etag,
                    'last_modified' => (int) ($meta['source_mtime'] ?? $this->mtime($sourceDisk, $relativePath)),
                    'hit' => true,
                ];
            }
        }

        $rendered = $producer();
        $mtime = $this->mtime($sourceDisk, $relativePath);
        $cache->put($payloadPath, $rendered['contents']);
        $cache->put($metaPath, json_encode([
            'mime' => $rendered['mime'],
            'source_mtime' => $mtime,
            'mode' => $mode,
            'format' => $format,
            'version' => self::VERSION,
        ]));

        return [
            'contents' => $rendered['contents'],
            'mime' => $rendered['mime'],
            'etag' => $etag,
            'last_modified' => $mtime,
            'hit' => false,
        ];
    }

    public function etag(Filesystem $sourceDisk, string $relativePath, string $mode, string $format): string
    {
        $mtime = $this->mtime($sourceDisk, $relativePath);
        $size = 0;
        try {
            $size = (int) $sourceDisk->size($relativePath);
        } catch (\Throwable) {
            $size = 0;
        }

        return hash('sha256', self::VERSION.'|'.$mode.'|'.$format.'|'.$relativePath.'|'.$mtime.'|'.$size);
    }

    public function payloadPath(string $etag): string
    {
        return 'media-derivatives/'.$etag.'.bin';
    }

    private function metaPath(string $etag): string
    {
        return 'media-derivatives/'.$etag.'.json';
    }

    private function mtime(Filesystem $sourceDisk, string $relativePath): int
    {
        try {
            return (int) $sourceDisk->lastModified($relativePath);
        } catch (\Throwable) {
            return time();
        }
    }
}

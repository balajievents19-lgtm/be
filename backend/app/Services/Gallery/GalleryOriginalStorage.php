<?php

namespace App\Services\Gallery;

use App\Models\GalleryItem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Private original gallery files live on the local (private) disk.
 * Public API must never expose original_path or private disk paths.
 */
final class GalleryOriginalStorage
{
    public const PRIVATE_DISK = 'local';

    public const PRIVATE_DIRECTORY = 'gallery/originals';

    public function hasPrivateOriginal(GalleryItem $item): bool
    {
        $path = $item->original_path;
        $disk = $item->original_disk ?: self::PRIVATE_DISK;

        return is_string($path)
            && $path !== ''
            && Storage::disk($disk)->exists($path);
    }

    /**
     * Copy an existing public original into private storage.
     * Does not delete the public file — caller decides after verification.
     *
     * @return bool|string True on success, or a short failure reason (tests may assert bool).
     */
    public function secureCopyFromPublic(GalleryItem $item): bool
    {
        $publicPath = $item->image;
        if (! is_string($publicPath) || $publicPath === '') {
            return false;
        }

        // Already secured.
        if ($this->hasPrivateOriginal($item)) {
            return true;
        }

        $publicDisk = Storage::disk('public');
        if (! $publicDisk->exists($publicPath)) {
            return false;
        }

        $extension = pathinfo($publicPath, PATHINFO_EXTENSION) ?: 'jpg';
        $privateRelative = self::PRIVATE_DIRECTORY.'/'.$item->id.'_'.Str::random(16).'.'.$extension;

        $contents = $publicDisk->get($publicPath);
        if ($contents === null) {
            return false;
        }

        Storage::disk(self::PRIVATE_DISK)->makeDirectory(self::PRIVATE_DIRECTORY);
        Storage::disk(self::PRIVATE_DISK)->put($privateRelative, $contents);

        if (! Storage::disk(self::PRIVATE_DISK)->exists($privateRelative)) {
            return false;
        }

        $item->forceFill([
            'original_path' => $privateRelative,
            'original_disk' => self::PRIVATE_DISK,
        ])->saveQuietly();

        return $this->hasPrivateOriginal($item->fresh() ?? $item);
    }

    /**
     * Remove the public original only after a verified private copy exists.
     * Ensures a separate public preview/thumbnail remains for browsing/Lightbox.
     */
    public function removePublicOriginalIfSecured(GalleryItem $item): bool
    {
        if (! $this->hasPrivateOriginal($item)) {
            return false;
        }

        $publicPath = $item->image;
        if (! is_string($publicPath) || $publicPath === '') {
            return true;
        }

        // Do not delete thumbnails / seo assets — only the stored image path when it
        // points under gallery/images (originals directory).
        if (! str_starts_with($publicPath, 'gallery/images/')) {
            return false;
        }

        $publicDisk = Storage::disk('public');
        $previewPath = $this->ensurePublicPreview($item, $publicPath);

        if ($publicDisk->exists($publicPath)) {
            $publicDisk->delete($publicPath);
        }

        $item->forceFill([
            'image' => $previewPath,
            'thumbnail' => $item->thumbnail && ! str_starts_with((string) $item->thumbnail, 'gallery/images/')
                ? $item->thumbnail
                : $previewPath,
        ])->saveQuietly();

        return true;
    }

    /**
     * Guarantee a public preview path that is NOT under gallery/images/.
     */
    private function ensurePublicPreview(GalleryItem $item, string $publicOriginalPath): string
    {
        $thumbnail = $item->thumbnail;
        if (
            is_string($thumbnail)
            && $thumbnail !== ''
            && $thumbnail !== $publicOriginalPath
            && ! str_starts_with($thumbnail, 'gallery/images/')
            && Storage::disk('public')->exists($thumbnail)
        ) {
            return $thumbnail;
        }

        $extension = pathinfo($publicOriginalPath, PATHINFO_EXTENSION) ?: 'jpg';
        $previewRelative = 'gallery/previews/'.$item->id.'_'.Str::random(10).'.'.$extension;

        $publicDisk = Storage::disk('public');
        if ($publicDisk->exists($publicOriginalPath)) {
            $publicDisk->put($previewRelative, $publicDisk->get($publicOriginalPath));
        } else {
            // Public original already gone — rebuild preview from private original.
            $disk = $item->original_disk ?: self::PRIVATE_DISK;
            $contents = Storage::disk($disk)->get((string) $item->original_path);
            $publicDisk->put($previewRelative, $contents);
        }

        return $previewRelative;
    }

    public function download(GalleryItem $item): StreamedResponse
    {
        if (! $this->hasPrivateOriginal($item)) {
            throw new RuntimeException('Original file is not available.');
        }

        $disk = $item->original_disk ?: self::PRIVATE_DISK;
        $path = (string) $item->original_path;
        $absolute = Storage::disk($disk)->path($path);

        // Path traversal guard: resolved path must stay under the disk root.
        $root = realpath(Storage::disk($disk)->path('')) ?: '';
        $resolved = realpath($absolute);
        if ($root === '' || $resolved === false || ! str_starts_with($resolved, $root)) {
            throw new RuntimeException('Invalid original file path.');
        }

        $mime = Storage::disk($disk)->mimeType($path) ?: 'application/octet-stream';
        $downloadName = $this->safeFilename($item);

        return Storage::disk($disk)->download($path, $downloadName, [
            'Content-Type' => $mime,
        ]);
    }

    private function safeFilename(GalleryItem $item): string
    {
        $base = Str::slug($item->title ?: 'gallery-image') ?: 'gallery-image';
        $ext = pathinfo((string) $item->original_path, PATHINFO_EXTENSION) ?: 'jpg';

        return $base.'.'.$ext;
    }
}

<?php

namespace App\Observers;

use App\Models\GalleryItem;
use App\Services\Gallery\GalleryOriginalStorage;
use App\Support\ContentCache;

/**
 * After admin uploads, move originals to private storage and drop public originals.
 * Never deletes records; never deletes thumbnails.
 */
class GalleryOriginalObserver
{
    private static bool $securing = false;

    public function __construct(
        private readonly GalleryOriginalStorage $originals
    ) {}

    public function saved(GalleryItem $item): void
    {
        if (self::$securing) {
            return;
        }

        $image = $item->image;
        if (! is_string($image) || $image === '') {
            return;
        }

        // Already pointing at a thumbnail/preview — nothing to secure from public images/.
        if (! str_starts_with($image, 'gallery/images/')) {
            return;
        }

        self::$securing = true;
        try {
            if (! $this->originals->secureCopyFromPublic($item->fresh() ?? $item)) {
                return;
            }

            $fresh = $item->fresh();
            if ($fresh !== null) {
                $this->originals->removePublicOriginalIfSecured($fresh);
                ContentCache::flush(ContentCache::GALLERY, ContentCache::GALLERY_CATEGORIES, ContentCache::SERVICES);
            }
        } finally {
            self::$securing = false;
        }
    }
}

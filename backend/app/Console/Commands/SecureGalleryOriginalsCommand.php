<?php

namespace App\Console\Commands;

use App\Models\GalleryItem;
use App\Services\Gallery\GalleryOriginalStorage;
use App\Support\ContentCache;
use Illuminate\Console\Command;

/**
 * Safely copy public gallery originals to private storage, then remove public originals.
 * Never deletes GalleryItem rows or thumbnails. Skips items that cannot be copied.
 */
class SecureGalleryOriginalsCommand extends Command
{
    protected $signature = 'gallery:secure-originals {--dry-run : Report only, do not change files}';

    protected $description = 'Copy gallery originals to private disk and remove public originals after verification';

    public function handle(GalleryOriginalStorage $originals): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $secured = 0;
        $skipped = 0;
        $failed = 0;

        GalleryItem::query()->orderBy('id')->chunkById(50, function ($items) use ($originals, $dryRun, &$secured, &$skipped, &$failed): void {
            foreach ($items as $item) {
                /** @var GalleryItem $item */
                $image = (string) $item->image;
                if ($originals->hasPrivateOriginal($item) && ! str_starts_with($image, 'gallery/images/')) {
                    $skipped++;

                    continue;
                }

                if (! str_starts_with($image, 'gallery/images/')) {
                    $skipped++;

                    continue;
                }

                if ($dryRun) {
                    $this->line("[dry-run] would secure GalleryItem #{$item->id} ({$item->image})");
                    $secured++;

                    continue;
                }

                if (! $originals->secureCopyFromPublic($item)) {
                    if ($originals->hasPrivateOriginal($item)) {
                        $originals->removePublicOriginalIfSecured($item->fresh());
                        $secured++;
                    } else {
                        $this->warn("SKIP/FAIL GalleryItem #{$item->id}: public original missing or copy failed — public file left untouched.");
                        $failed++;
                    }

                    continue;
                }

                $originals->removePublicOriginalIfSecured($item->fresh());
                $secured++;
                $this->info("Secured GalleryItem #{$item->id}");
            }
        });

        if (! $dryRun) {
            ContentCache::flush(ContentCache::GALLERY, ContentCache::GALLERY_CATEGORIES);
        }

        $this->newLine();
        $this->info("Done. secured/processed={$secured} skipped={$skipped} failed={$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Support\ContentCache;
use Illuminate\Console\Command;

/**
 * Reattach gallery items to categories by slug after a category-id remap.
 * Does not create/delete items or restore soft-deleted photos.
 */
class ReattachGalleryCategoriesCommand extends Command
{
    protected $signature = 'gallery:reattach-categories {--dry-run : Report only}';

    protected $description = 'Point gallery items at categories using the real CMS slug map';

    /**
     * Item slug → category slug from the local Balaji Royal Events CMS.
     *
     * @var array<string, string>
     */
    private const ITEM_CATEGORY = [
        'royal-wedding-mandap-decoration-alsisar-mahal' => 'florist_decor',
        'bridal-entry-moments' => 'dj-sound',
        'reception-celebration' => 'mega-structures-tent-house',
        'mehndi-night-setup' => 'florist_decor',
        'floral-stage-backdrop' => 'baraat-entry-cars',
        'royal-stage-lighting' => 'sfx-effects-entry',
        'traditional-stage-decor' => 'wedding-films-photography',
        'modern-stage-design' => 'fine-dining-catering',
        'luxury-outdoor-tent' => 'guest-hospitality-stays',
        'garden-tent-arrangement' => 'mehndi-bridal-mehndi-art',
        'night-tent-ambience' => 'sangeet',
        'vip-tent-lounge' => 'corporate-conferences-events',
        'buffet-presentation' => 'wedding_planner',
        'live-counter-setup' => 'dj-sound',
        'dessert-display' => 'mega-structures-tent-house',
        'dining-table-styling' => 'florist_decor',
        'candid-couple-portraits' => 'baraat-entry-cars',
        'ceremony-coverage' => 'sfx-effects-entry',
        'reception-photography' => 'wedding-films-photography',
        'family-group-moments' => 'fine-dining-catering',
        'royal-haldi-ceremony-decoration' => 'haldi-carnival',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $updated = 0;
        $skipped = 0;

        $categories = GalleryCategory::query()
            ->withTrashed()
            ->get()
            ->keyBy('slug');

        GalleryItem::query()->withTrashed()->orderBy('id')->each(function (GalleryItem $item) use ($categories, $dryRun, &$updated, &$skipped): void {
            $targetSlug = self::ITEM_CATEGORY[$item->slug] ?? null;
            if ($targetSlug === null) {
                $skipped++;
                $this->line("skip unknown item slug {$item->slug}");

                return;
            }

            $category = $categories->get($targetSlug);
            if ($category === null) {
                $this->warn("missing category slug {$targetSlug} for item {$item->slug}");
                $skipped++;

                return;
            }

            if ((int) $item->gallery_category_id === (int) $category->id) {
                $skipped++;

                return;
            }

            $this->line("item {$item->id} {$item->slug}: category {$item->gallery_category_id} → {$category->id} ({$targetSlug})");
            if (! $dryRun) {
                $item->forceFill(['gallery_category_id' => $category->id])->saveQuietly();
            }
            $updated++;
        });

        if (! $dryRun && $updated > 0) {
            ContentCache::flush(ContentCache::GALLERY, ContentCache::GALLERY_CATEGORIES);
        }

        $this->info("updated={$updated} skipped={$skipped} dry-run=".($dryRun ? 'yes' : 'no'));

        return self::SUCCESS;
    }
}

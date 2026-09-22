<?php

namespace Tests\Feature;

use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReattachGalleryCategoriesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_reattaches_orphaned_item_to_category_by_slug(): void
    {
        $wrong = GalleryCategory::query()->create([
            'name' => 'Orphan bucket',
            'slug' => 'orphan-bucket',
            'status' => true,
            'sort_order' => 99,
        ]);
        $florist = GalleryCategory::query()->create([
            'name' => 'Decorator & Florist',
            'slug' => 'florist_decor',
            'status' => true,
            'sort_order' => 1,
        ]);

        $item = GalleryItem::query()->create([
            'gallery_category_id' => $wrong->id,
            'title' => 'Royal Wedding Mandap Decoration at Alsisar Mahal',
            'slug' => 'royal-wedding-mandap-decoration-alsisar-mahal',
            'image' => 'gallery/thumbnails/cover.jpg',
            'thumbnail' => 'gallery/thumbnails/cover.jpg',
            'status' => true,
            'sort_order' => 1,
        ]);

        $this->artisan('gallery:reattach-categories')
            ->assertSuccessful();

        $this->assertSame($florist->id, $item->fresh()->gallery_category_id);
    }
}

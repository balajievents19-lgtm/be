<?php

namespace Tests\Feature\Api;

use App\Models\GalleryItem;
use App\Support\ContentCache;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\Support\CreatesWebsiteContent;
use Tests\TestCase;

class GalleryServiceLinkTest extends TestCase
{
    use CreatesWebsiteContent;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->seedSettings();
    }

    public function test_gallery_item_belongs_to_one_category_and_many_services(): void
    {
        $decor = $this->createService(['name' => 'Wedding Décor & Design', 'slug' => 'wedding-decor-design', 'sort_order' => 2]);
        $tent = $this->createService(['name' => 'Tent, Mandap & Venue Setup', 'slug' => 'tent-mandap-venue-setup', 'sort_order' => 3]);
        $stage = $this->createService(['name' => 'Stage, Lighting & LED', 'slug' => 'stage-lighting-led', 'sort_order' => 4]);
        $planning = $this->createService(['name' => 'Wedding Planning', 'slug' => 'wedding-planning', 'sort_order' => 1]);

        $item = $this->createGalleryItem([
            'title' => 'Royal Mandap',
            'slug' => 'royal-mandap',
            'image' => 'gallery/previews/royal-mandap.jpg',
        ]);
        $item->services()->sync([$decor->id, $tent->id, $stage->id]);

        $this->assertSame(1, GalleryItem::query()->whereKey($item->id)->count());
        $this->assertCount(3, $item->services()->get());
        $this->assertSame($item->image, $item->fresh()->image);

        $this->getJson('/api/gallery/categories/'.$item->category->slug)
            ->assertOk()
            ->assertJsonPath('data.items.0.slug', 'royal-mandap');

        foreach ([$decor, $tent, $stage] as $service) {
            $response = $this->getJson('/api/services/'.$service->slug);
            $response->assertOk();
            $slugs = collect($response->json('data.related_gallery'))->pluck('slug');
            $this->assertTrue($slugs->contains('royal-mandap'), $service->slug.' missing photo');
        }

        $planningSlugs = collect($this->getJson('/api/services/'.$planning->slug)->json('data.related_gallery'))->pluck('slug');
        $this->assertFalse($planningSlugs->contains('royal-mandap'));
    }

    public function test_detaching_one_service_keeps_photo_and_other_links(): void
    {
        $decor = $this->createService(['name' => 'Wedding Décor & Design', 'slug' => 'wedding-decor-design', 'sort_order' => 2]);
        $stage = $this->createService(['name' => 'Stage, Lighting & LED', 'slug' => 'stage-lighting-led', 'sort_order' => 4]);
        $item = $this->createGalleryItem(['slug' => 'royal-mandap', 'title' => 'Royal Mandap']);
        $item->services()->sync([$decor->id, $stage->id]);

        $item->services()->detach($stage->id);

        $this->assertDatabaseHas('gallery_items', ['id' => $item->id, 'slug' => 'royal-mandap']);
        $this->assertDatabaseHas('gallery_item_service', [
            'gallery_item_id' => $item->id,
            'service_id' => $decor->id,
        ]);
        $this->assertDatabaseMissing('gallery_item_service', [
            'gallery_item_id' => $item->id,
            'service_id' => $stage->id,
        ]);

        Cache::forget(ContentCache::SERVICES);
        ContentCache::flush(ContentCache::SERVICES);

        $this->assertTrue(
            collect($this->getJson('/api/services/'.$decor->slug)->json('data.related_gallery'))->pluck('slug')->contains('royal-mandap')
        );
        $this->assertFalse(
            collect($this->getJson('/api/services/'.$stage->slug)->json('data.related_gallery'))->pluck('slug')->contains('royal-mandap')
        );
    }

    public function test_duplicate_pivot_is_rejected(): void
    {
        $service = $this->createService();
        $item = $this->createGalleryItem();
        $item->services()->attach($service->id);

        $this->expectException(QueryException::class);
        $item->services()->attach($service->id);
    }

    public function test_deleting_photo_removes_relationships_not_services(): void
    {
        $service = $this->createService();
        $item = $this->createGalleryItem(['slug' => 'to-delete']);
        $item->services()->attach($service->id);

        $item->forceDelete();

        $this->assertDatabaseMissing('gallery_items', ['id' => $item->id]);
        $this->assertDatabaseMissing('gallery_item_service', ['gallery_item_id' => $item->id]);
        $this->assertDatabaseHas('services', ['id' => $service->id]);
    }

    public function test_inactive_gallery_item_is_hidden_from_service_and_category(): void
    {
        $service = $this->createService();
        $item = $this->createGalleryItem(['slug' => 'hidden-photo', 'status' => false]);
        $item->services()->attach($service->id);

        $this->assertFalse(
            collect($this->getJson('/api/services/'.$service->slug)->json('data.related_gallery'))->pluck('slug')->contains('hidden-photo')
        );
        $this->getJson('/api/gallery/categories/'.$item->category->slug)
            ->assertOk()
            ->assertJsonCount(0, 'data.items');
    }

    public function test_service_gallery_query_does_not_n_plus_one(): void
    {
        $service = $this->createService();
        for ($i = 1; $i <= 5; $i++) {
            $item = $this->createGalleryItem([
                'title' => 'Photo '.$i,
                'slug' => 'photo-'.$i,
            ]);
            $item->services()->attach($service->id);
        }

        ContentCache::flush(ContentCache::SERVICES);
        $count = 0;
        DB::listen(function () use (&$count): void {
            $count++;
        });

        $this->getJson('/api/services/'.$service->slug)
            ->assertOk()
            ->assertJsonCount(5, 'data.related_gallery');

        $this->assertLessThan(12, $count);
    }
}

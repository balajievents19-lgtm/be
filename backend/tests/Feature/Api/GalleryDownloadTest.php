<?php

namespace Tests\Feature\Api;

use App\Models\Customer;
use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Services\Gallery\GalleryOriginalStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GalleryDownloadTest extends TestCase
{
    use RefreshDatabase;

    private function makeItemWithPrivateOriginal(): GalleryItem
    {
        Storage::fake('local');
        Storage::fake('public');

        $category = GalleryCategory::query()->create([
            'name' => 'Wedding',
            'slug' => 'wedding',
            'status' => true,
            'sort_order' => 1,
        ]);

        Storage::disk('public')->put('gallery/thumbnails/thumb.jpg', 'thumb-bytes');
        Storage::disk('local')->put('gallery/originals/1_secret.jpg', 'original-bytes-high-res');

        return GalleryItem::query()->create([
            'gallery_category_id' => $category->id,
            'title' => 'Protected Photo',
            'slug' => 'protected-photo',
            'image' => 'gallery/thumbnails/thumb.jpg',
            'thumbnail' => 'gallery/thumbnails/thumb.jpg',
            'original_path' => 'gallery/originals/1_secret.jpg',
            'original_disk' => 'local',
            'status' => true,
            'sort_order' => 1,
        ]);
    }

    public function test_guest_cannot_download_original(): void
    {
        $item = $this->makeItemWithPrivateOriginal();

        $this->getJson('/api/gallery/items/'.$item->id.'/download')
            ->assertUnauthorized();
    }

    public function test_customer_can_download_original(): void
    {
        $item = $this->makeItemWithPrivateOriginal();
        $customer = Customer::factory()->create();

        $response = $this->actingAs($customer, 'customer')
            ->get('/api/gallery/items/'.$item->id.'/download');

        $response->assertOk();
        $this->assertStringContainsString('attachment', (string) $response->headers->get('content-disposition'));
    }

    public function test_invalid_gallery_item_returns_404(): void
    {
        $customer = Customer::factory()->create();

        $this->actingAs($customer, 'customer')
            ->getJson('/api/gallery/items/999999/download')
            ->assertNotFound();
    }

    public function test_public_gallery_api_does_not_expose_private_path(): void
    {
        $item = $this->makeItemWithPrivateOriginal();

        $response = $this->getJson('/api/gallery/'.$item->slug);
        $response->assertOk();
        $json = $response->json('data');

        $this->assertArrayNotHasKey('original_path', $json);
        $this->assertArrayNotHasKey('original_disk', $json);
        $this->assertStringNotContainsString('gallery/originals', json_encode($json));
        $this->assertTrue($json['download_available']);
    }

    public function test_secure_copy_moves_public_original_privately(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $category = GalleryCategory::query()->create([
            'name' => 'Wedding',
            'slug' => 'wedding-2',
            'status' => true,
            'sort_order' => 1,
        ]);

        Storage::disk('public')->put('gallery/images/old.jpg', 'public-original');
        Storage::disk('public')->put('gallery/thumbnails/thumb.jpg', 'thumb');

        $item = GalleryItem::withoutEvents(function () use ($category) {
            return GalleryItem::query()->create([
                'gallery_category_id' => $category->id,
                'title' => 'Legacy Photo',
                'slug' => 'legacy-photo',
                'image' => 'gallery/images/old.jpg',
                'thumbnail' => 'gallery/thumbnails/thumb.jpg',
                'status' => true,
                'sort_order' => 1,
            ]);
        });

        $service = app(GalleryOriginalStorage::class);
        $fresh = $item->fresh();
        $this->assertNotNull($fresh);
        $this->assertTrue(
            Storage::disk('public')->exists('gallery/images/old.jpg'),
            'public original missing before secure: '.implode(',', Storage::disk('public')->allFiles())
        );
        $ok = $service->secureCopyFromPublic($fresh);
        $this->assertTrue(
            $ok,
            'secureCopy failed; item='.json_encode($item->fresh()?->only(['id', 'image', 'original_path', 'original_disk'])).
            ' publicFiles='.implode(',', Storage::disk('public')->allFiles()).
            ' localFiles='.implode(',', Storage::disk('local')->allFiles())
        );
        $item->refresh();
        $this->assertTrue($service->hasPrivateOriginal($item));
        $this->assertTrue($service->removePublicOriginalIfSecured($item->fresh()));
        $item->refresh();

        $this->assertFalse(Storage::disk('public')->exists('gallery/images/old.jpg'));
        $this->assertTrue(Storage::disk('public')->exists('gallery/thumbnails/thumb.jpg'));
        $this->assertNotSame('gallery/images/old.jpg', $item->image);
        $this->assertFalse(str_starts_with((string) $item->image, 'gallery/images/'));
        $this->assertTrue(Storage::disk('public')->exists((string) $item->image));
    }
}

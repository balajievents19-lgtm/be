<?php

namespace Tests\Feature\Api;

use App\Models\Customer;
use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Services\Gallery\GalleryOriginalStorage;
use App\Support\Brand;
use App\Support\Media\PublicStorageUrl;
use GdImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GalleryDownloadTest extends TestCase
{
    use RefreshDatabase;

    private function jpegBytes(): string
    {
        $image = imagecreatetruecolor(240, 160);
        $this->assertNotFalse($image);
        $fill = imagecolorallocate($image, 8, 10, 16);
        imagefilledrectangle($image, 0, 0, 239, 159, $fill);
        ob_start();
        imagejpeg($image, null, 95);
        $binary = (string) ob_get_clean();
        imagedestroy($image);

        return $binary;
    }

    private function makeItemWithPrivateOriginal(array $overrides = []): GalleryItem
    {
        Storage::fake('local');
        Storage::fake('public');

        $category = GalleryCategory::query()->create([
            'name' => 'Wedding',
            'slug' => 'wedding-'.uniqid(),
            'status' => true,
            'sort_order' => 1,
        ]);

        $original = $overrides['original_bytes'] ?? $this->jpegBytes();
        unset($overrides['original_bytes']);

        Storage::disk('public')->put('gallery/thumbnails/thumb.jpg', $this->jpegBytes());
        Storage::disk('local')->put('gallery/originals/1_secret.jpg', $original);

        return GalleryItem::query()->create(array_merge([
            'gallery_category_id' => $category->id,
            'title' => 'Protected Photo',
            'slug' => 'protected-photo-'.uniqid(),
            'image' => 'gallery/thumbnails/thumb.jpg',
            'thumbnail' => 'gallery/thumbnails/thumb.jpg',
            'original_path' => 'gallery/originals/1_secret.jpg',
            'original_disk' => 'local',
            'status' => true,
            'sort_order' => 1,
        ], $overrides));
    }

    public function test_guest_cannot_download_original(): void
    {
        $item = $this->makeItemWithPrivateOriginal();

        $this->getJson('/api/gallery/items/'.$item->id.'/download')
            ->assertUnauthorized();
    }

    public function test_logged_out_direct_storage_url_does_not_yield_original(): void
    {
        $this->makeItemWithPrivateOriginal();

        $this->get('/storage/gallery/originals/1_secret.jpg')->assertForbidden();
        $this->get('/storage/gallery/thumbnails/thumb.jpg')->assertForbidden();
    }

    public function test_customer_download_is_watermarked_and_does_not_change_original(): void
    {
        $original = $this->jpegBytes();
        $item = $this->makeItemWithPrivateOriginal(['original_bytes' => $original]);
        $customer = Customer::factory()->create();

        $response = $this->actingAs($customer, 'customer')
            ->get('/api/gallery/items/'.$item->id.'/download');

        $response->assertOk();
        $this->assertStringContainsString('attachment', (string) $response->headers->get('content-disposition'));
        $this->assertStringNotContainsString('gallery/originals', (string) $response->headers->get('content-disposition'));

        $downloaded = $response->getContent();
        $this->assertNotSame('', $downloaded);
        $this->assertNotSame($original, $downloaded);
        $this->assertSame($original, Storage::disk('local')->get('gallery/originals/1_secret.jpg'));
        $this->assertContainsTiledBrandWatermark($downloaded);
    }

    public function test_customer_cannot_bypass_watermark_via_protected_media_or_api_preview(): void
    {
        $original = $this->jpegBytes();
        $item = $this->makeItemWithPrivateOriginal(['original_bytes' => $original]);
        $customer = Customer::factory()->create();

        $previewUrl = PublicStorageUrl::make('gallery/thumbnails/thumb.jpg');
        $this->assertProtectedDisplayUrl($previewUrl, 'gallery/thumbnails/thumb.jpg');
        $token = basename((string) parse_url((string) $previewUrl, PHP_URL_PATH));

        $preview = $this->actingAs($customer, 'customer')
            ->get('/protected-media/'.$token);
        $preview->assertOk();
        $this->assertContainsTiledBrandWatermark($preview->getContent());

        $api = $this->getJson('/api/gallery/'.$item->slug)->assertOk()->json('data');
        $this->assertArrayNotHasKey('original_path', $api);
        $this->assertStringStartsWith('/protected-media/', (string) $api['image']);
        $this->assertStringNotContainsString('gallery/originals', json_encode($api));
    }

    public function test_customer_cannot_download_inactive_gallery_item(): void
    {
        $item = $this->makeItemWithPrivateOriginal(['status' => false]);
        $customer = Customer::factory()->create();

        $this->actingAs($customer, 'customer')
            ->getJson('/api/gallery/items/'.$item->id.'/download')
            ->assertNotFound();
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

    private function assertContainsTiledBrandWatermark(string $bytes): void
    {
        $decoded = @imagecreatefromstring($bytes);
        $this->assertInstanceOf(GdImage::class, $decoded);

        $width = imagesx($decoded);
        $height = imagesy($decoded);
        $hits = 0;

        foreach ([0.18, 0.50, 0.82] as $fy) {
            foreach ([0.18, 0.50, 0.82] as $fx) {
                $cx = (int) round($width * $fx);
                $cy = (int) round($height * $fy);
                $found = false;
                for ($x = max(0, $cx - 24); $x < min($width, $cx + 24); $x += 2) {
                    for ($y = max(0, $cy - 16); $y < min($height, $cy + 16); $y += 2) {
                        $rgb = imagecolorat($decoded, $x, $y);
                        $r = ($rgb >> 16) & 0xFF;
                        $g = ($rgb >> 8) & 0xFF;
                        $b = $rgb & 0xFF;
                        if ($r > 90 || $g > 90 || $b > 90) {
                            $found = true;
                            break 2;
                        }
                    }
                }
                if ($found) {
                    $hits++;
                }
            }
        }

        imagedestroy($decoded);
        $this->assertGreaterThanOrEqual(5, $hits, 'Expected a repeated '.Brand::NAME.' watermark across the image.');
    }
}

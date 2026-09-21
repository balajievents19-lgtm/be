<?php

namespace Tests\Feature;

use App\Models\ServicePackage;
use App\Services\Gallery\GalleryOriginalStorage;
use App\Services\Maintenance\WebsiteCleanupService;
use App\Support\ContentCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesWebsiteContent;
use Tests\TestCase;

class WebsiteCleanupTest extends TestCase
{
    use CreatesWebsiteContent;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::fake('local');
        Cache::flush();
        $this->seedSettings([
            'logo' => 'settings/brand/logo.png',
        ]);
    }

    public function test_referenced_and_active_media_are_not_deleted(): void
    {
        Storage::disk('public')->put('gallery/previews/active-gallery.jpg', 'gallery-bytes');
        Storage::disk('public')->put('services/featured/active-service.jpg', 'service-bytes');
        Storage::disk('public')->put('service-packages/active-package.jpg', 'package-bytes');
        Storage::disk('public')->put('blog/featured/active-blog.jpg', 'blog-bytes');
        Storage::disk('public')->put('settings/brand/logo.png', 'logo-bytes');
        Storage::disk('public')->put('studio/uploads/referenced-studio.jpg', 'studio-ref');
        Storage::disk('local')->put(GalleryOriginalStorage::PRIVATE_DIRECTORY.'/secret-original.jpg', 'original-bytes');

        $gallery = $this->createGalleryItem([
            'image' => 'gallery/previews/active-gallery.jpg',
            'original_path' => GalleryOriginalStorage::PRIVATE_DIRECTORY.'/secret-original.jpg',
            'original_disk' => GalleryOriginalStorage::PRIVATE_DISK,
        ]);
        $service = $this->createService([
            'featured_image' => 'services/featured/active-service.jpg',
        ]);
        $package = ServicePackage::query()->create([
            'service_id' => $service->id,
            'name' => 'Royal Package',
            'slug' => 'royal-package',
            'image' => 'service-packages/active-package.jpg',
            'status' => true,
        ]);
        $blog = $this->createBlogPost([
            'featured_image' => 'blog/featured/active-blog.jpg',
        ]);
        $this->createGalleryItem([
            'title' => 'Studio leftover still in use',
            'slug' => 'studio-leftover-in-use',
            'image' => 'studio/uploads/referenced-studio.jpg',
            'status' => true,
            'homepage_featured' => false,
            'featured' => false,
            'sort_order' => 9,
        ]);

        $galleryCount = (int) $gallery->newQuery()->count();
        $serviceCount = (int) $service->newQuery()->count();

        $result = app(WebsiteCleanupService::class)->run();

        $this->assertFalse($result->failed);
        $this->assertTrue(Storage::disk('public')->exists('gallery/previews/active-gallery.jpg'));
        $this->assertTrue(Storage::disk('public')->exists('services/featured/active-service.jpg'));
        $this->assertTrue(Storage::disk('public')->exists('service-packages/active-package.jpg'));
        $this->assertTrue(Storage::disk('public')->exists('blog/featured/active-blog.jpg'));
        $this->assertTrue(Storage::disk('public')->exists('settings/brand/logo.png'));
        $this->assertTrue(Storage::disk('public')->exists('studio/uploads/referenced-studio.jpg'));
        $this->assertTrue(Storage::disk('local')->exists(GalleryOriginalStorage::PRIVATE_DIRECTORY.'/secret-original.jpg'));
        $this->assertSame($galleryCount, $gallery->newQuery()->count());
        $this->assertSame($serviceCount, $service->newQuery()->count());
        $this->assertDatabaseHas('service_packages', ['id' => $package->id, 'image' => 'service-packages/active-package.jpg']);
        $this->assertDatabaseHas('blog_posts', ['id' => $blog->id, 'featured_image' => 'blog/featured/active-blog.jpg']);
        $this->assertDatabaseHas('settings', ['logo' => 'settings/brand/logo.png']);
    }

    public function test_unknown_and_unreferenced_protected_media_are_not_deleted(): void
    {
        Storage::disk('public')->put('misc/unknown-file.jpg', 'unknown');
        Storage::disk('public')->put('gallery/images/not-in-database.jpg', 'unreferenced-gallery');
        Storage::disk('public')->put('services/featured/not-in-database.jpg', 'unreferenced-service');

        $result = app(WebsiteCleanupService::class)->run();

        $this->assertSame(0, $result->filesRemoved);
        $this->assertTrue(Storage::disk('public')->exists('misc/unknown-file.jpg'));
        $this->assertTrue(Storage::disk('public')->exists('gallery/images/not-in-database.jpg'));
        $this->assertTrue(Storage::disk('public')->exists('services/featured/not-in-database.jpg'));
    }

    public function test_expired_temporary_file_is_removed_and_fresh_temp_is_kept(): void
    {
        Storage::disk('local')->put('livewire-tmp/expired.bin', 'expired-temp');
        Storage::disk('local')->put('livewire-tmp/fresh.bin', 'fresh-temp');
        touch(Storage::disk('local')->path('livewire-tmp/expired.bin'), now()->subHours(25)->getTimestamp());
        touch(Storage::disk('local')->path('livewire-tmp/fresh.bin'), now()->getTimestamp());

        $result = app(WebsiteCleanupService::class)->run();

        $this->assertFalse($result->failed);
        $this->assertGreaterThanOrEqual(1, $result->temporaryRemoved);
        $this->assertFalse(Storage::disk('local')->exists('livewire-tmp/expired.bin'));
        $this->assertTrue(Storage::disk('local')->exists('livewire-tmp/fresh.bin'));
    }

    public function test_confirmed_orphan_studio_file_is_removed(): void
    {
        Storage::disk('public')->put('studio/uploads/confirmed-orphan.jpg', 'orphan-bytes');

        $result = app(WebsiteCleanupService::class)->run();

        $this->assertFalse($result->failed);
        $this->assertSame(1, $result->orphansRemoved);
        $this->assertSame(1, $result->filesRemoved);
        $this->assertFalse(Storage::disk('public')->exists('studio/uploads/confirmed-orphan.jpg'));
        $this->assertGreaterThan(0, $result->bytesFreed);
    }

    public function test_safe_cache_is_cleared_without_touching_database_rows(): void
    {
        $this->createService();
        Cache::forever(ContentCache::HOME, ['payload' => 'stale']);
        Cache::forever(ContentCache::SETTINGS, ['payload' => 'stale']);

        $result = app(WebsiteCleanupService::class)->run();

        $this->assertTrue($result->cacheCleared);
        $this->assertFalse(Cache::has(ContentCache::HOME));
        $this->assertFalse(Cache::has(ContentCache::SETTINGS));
        $this->assertDatabaseCount('services', 1);
        $this->assertDatabaseCount('settings', 1);
    }

    public function test_already_clean_result_message(): void
    {
        $result = app(WebsiteCleanupService::class)->run();

        $this->assertTrue($result->isClean());
        $this->assertSame('Website is already clean. No unnecessary files were removed.', $result->notificationTitle());
        $this->assertStringContainsString('Safe cache cleared: Yes', $result->notificationBody());
        $this->assertStringContainsString('Files removed: 0', $result->notificationBody());
    }
}

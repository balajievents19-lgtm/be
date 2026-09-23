<?php

namespace Tests\Feature\Api;

use App\Enums\GalleryMediaType;
use App\Enums\GalleryVideoSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesWebsiteContent;
use Tests\TestCase;

class GalleryVideoApiTest extends TestCase
{
    use CreatesWebsiteContent;
    use RefreshDatabase;

    public function test_existing_image_records_default_to_image_media_type(): void
    {
        $item = $this->createGalleryItem();

        $this->assertSame(GalleryMediaType::Image, $item->fresh()->media_type);

        $this->getJson('/api/gallery')
            ->assertOk()
            ->assertJsonPath('data.0.media_type', 'image')
            ->assertJsonPath('data.0.slug', 'reception-hall');
    }

    public function test_youtube_video_is_listed_with_embed_and_is_not_stored_as_a_file(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $item = $this->createGalleryItem([
            'title' => 'Haldi Film',
            'slug' => 'haldi-film',
            'image' => '',
            'media_type' => GalleryMediaType::Video,
            'video_source' => GalleryVideoSource::Youtube,
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);

        $this->assertSame(GalleryMediaType::Video, $item->fresh()->media_type);
        $this->assertSame([], Storage::disk('local')->allFiles());

        $json = $this->getJson('/api/gallery/categories/weddings')->assertOk()->json('data.items.0');
        $this->assertSame('video', $json['media_type']);
        $this->assertSame('youtube', $json['video_source']);
        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', $json['embed']['embed_url']);
        $this->assertSame('dQw4w9WgXcQ', $json['video_id']);
        $this->assertNull($json['video_url']);
        $this->assertNull($json['youtube_url']);
        $this->assertNull($json['embed']['open_url']);
        $this->assertNull($json['embed']['cta_label']);
        $this->assertStringNotContainsString('Watch on YouTube', json_encode($json));
        $this->assertStringNotContainsString('youtube.com/watch', json_encode($json));
        $this->assertFalse($json['download_available']);
        $this->assertArrayNotHasKey('original_path', $json);
        $this->assertStringNotContainsString('gallery/originals', json_encode($json));
    }

    public function test_instagram_facebook_and_other_link_payloads(): void
    {
        $this->createGalleryItem([
            'title' => 'IG Reel',
            'slug' => 'ig-reel',
            'image' => '',
            'media_type' => GalleryMediaType::Video,
            'video_source' => GalleryVideoSource::Instagram,
            'video_url' => 'https://www.instagram.com/reel/AbC123xyz/',
        ]);
        $this->createGalleryItem([
            'title' => 'FB Clip',
            'slug' => 'fb-clip',
            'image' => '',
            'media_type' => GalleryMediaType::Video,
            'video_source' => GalleryVideoSource::Facebook,
            'video_url' => 'https://www.facebook.com/watch/?v=123456789',
        ]);
        $this->createGalleryItem([
            'title' => 'External Page',
            'slug' => 'external-page',
            'image' => '',
            'media_type' => GalleryMediaType::Video,
            'video_source' => GalleryVideoSource::Other,
            'video_url' => 'https://example.com/our-film',
        ]);

        $items = collect($this->getJson('/api/gallery/categories/weddings')->assertOk()->json('data.items'))
            ->keyBy('slug');

        $this->assertSame('https://www.instagram.com/reel/AbC123xyz/embed/', $items['ig-reel']['embed']['embed_url']);
        $this->assertStringStartsWith('https://www.facebook.com/plugins/video.php', $items['fb-clip']['embed']['embed_url']);
        $this->assertSame('link', $items['external-page']['embed']['mode']);
        $this->assertNull($items['external-page']['embed']['embed_url']);
        $this->assertSame('https://example.com/our-film', $items['external-page']['video_url']);
    }

    public function test_video_keeps_category_and_service_assignment(): void
    {
        $service = $this->createService([
            'name' => 'Haldi Decor',
            'slug' => 'haldi-decor',
        ]);

        $item = $this->createGalleryItem([
            'title' => 'Service Video',
            'slug' => 'service-video',
            'image' => '',
            'media_type' => GalleryMediaType::Video,
            'video_source' => GalleryVideoSource::Youtube,
            'video_url' => 'https://youtu.be/dQw4w9WgXcQ',
            'description' => 'Ceremony film',
        ]);
        $item->services()->sync([$service->id]);

        $this->assertTrue($item->fresh()->services()->whereKey($service->id)->exists());
        $this->getJson('/api/gallery/service-video')
            ->assertOk()
            ->assertJsonPath('data.media_type', 'video')
            ->assertJsonPath('data.description', 'Ceremony film');
    }

    public function test_inactive_video_is_hidden_and_protected_image_still_works(): void
    {
        $this->createGalleryItem();
        $this->createGalleryItem([
            'title' => 'Draft Video',
            'slug' => 'draft-video',
            'image' => '',
            'media_type' => GalleryMediaType::Video,
            'video_source' => GalleryVideoSource::Youtube,
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'status' => false,
        ]);

        $this->getJson('/api/gallery')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/gallery/draft-video')->assertNotFound();
        $this->assertProtectedDisplayUrl(
            $this->getJson('/api/gallery')->json('data.0.image'),
            'gallery/reception.jpg'
        );
    }
}

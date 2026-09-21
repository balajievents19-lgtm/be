<?php

namespace Tests\Feature\Api;

use App\Models\ExternalMedia;
use App\Services\Media\ExternalMediaUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExternalMediaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_published_external_media_only(): void
    {
        ExternalMedia::query()->create([
            'title' => 'Wedding Film',
            'media_type' => 'video',
            'provider' => 'youtube',
            'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'status' => true,
            'sort_order' => 1,
        ]);

        ExternalMedia::query()->create([
            'title' => 'Hidden',
            'media_type' => 'video',
            'provider' => 'youtube',
            'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'status' => false,
            'sort_order' => 2,
        ]);

        $this->getJson('/api/external-media')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Wedding Film')
            ->assertJsonPath('data.0.mode', 'embed')
            ->assertJsonPath('data.0.embed_url', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ');
    }

    public function test_instagram_and_drive_use_safe_link_mode(): void
    {
        ExternalMedia::query()->create([
            'title' => 'IG Post',
            'media_type' => 'social_post',
            'provider' => 'instagram',
            'url' => 'https://www.instagram.com/p/ABC123/',
            'status' => true,
            'sort_order' => 1,
        ]);
        ExternalMedia::query()->create([
            'title' => 'Drive File',
            'media_type' => 'external',
            'provider' => 'google_drive',
            'url' => 'https://drive.google.com/file/d/xyz/view',
            'status' => true,
            'sort_order' => 2,
            'homepage_featured' => true,
        ]);

        $response = $this->getJson('/api/external-media');
        $response->assertOk()->assertJsonCount(2, 'data');

        $ig = collect($response->json('data'))->firstWhere('provider', 'instagram');
        $this->assertSame('link', $ig['mode']);
        $this->assertNull($ig['embed_url']);
        $this->assertSame('View on Instagram', $ig['cta_label']);

        $this->getJson('/api/external-media?homepage=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.provider', 'google_drive');
    }

    public function test_rejects_javascript_urls_in_resolver(): void
    {
        $resolved = ExternalMediaUrl::resolve('javascript:alert(1)');
        $this->assertFalse($resolved['valid']);
        $this->assertNull($resolved['embed_url']);
    }
}

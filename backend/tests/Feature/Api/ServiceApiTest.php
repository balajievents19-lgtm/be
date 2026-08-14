<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Support\CreatesWebsiteContent;
use Tests\TestCase;

class ServiceApiTest extends TestCase
{
    use CreatesWebsiteContent;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->seedSettings();
    }

    public function test_services_index_lists_active_only(): void
    {
        $this->createService();
        $this->createService([
            'name' => 'Hidden',
            'slug' => 'hidden',
            'status' => false,
            'show_on_homepage' => false,
        ]);

        $response = $this->getJson('/api/services');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'wedding-planning')
            ->assertJsonMissingPath('data.0.full_description');
    }

    public function test_services_show_returns_detail_fields(): void
    {
        $this->createService([
            'seo_title' => 'Wedding SEO',
            'seo_description' => 'Plan your wedding',
        ]);

        $this->getJson('/api/services/wedding-planning')
            ->assertOk()
            ->assertJsonPath('data.slug', 'wedding-planning')
            ->assertJsonPath('data.full_description', '<p>Details</p>')
            ->assertJsonPath('data.seo.title', 'Wedding SEO')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'slug',
                    'full_description',
                    'banner_image',
                    'gallery_images',
                    'seo' => ['title', 'description', 'keywords', 'opengraph_image'],
                ],
            ]);
    }

    public function test_services_show_returns_404_for_inactive_or_missing(): void
    {
        $this->createService(['status' => false]);

        $this->getJson('/api/services/wedding-planning')->assertNotFound();
        $this->getJson('/api/services/missing')->assertNotFound();
    }
}

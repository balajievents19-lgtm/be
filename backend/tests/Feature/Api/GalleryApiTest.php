<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Support\CreatesWebsiteContent;
use Tests\TestCase;

class GalleryApiTest extends TestCase
{
    use CreatesWebsiteContent;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->seedSettings();
    }

    public function test_gallery_index_and_show(): void
    {
        $this->createGalleryItem();

        $this->getJson('/api/gallery')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'reception-hall')
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'title',
                        'slug',
                        'image',
                        'category' => ['id', 'name', 'slug'],
                    ],
                ],
            ]);

        $this->getJson('/api/gallery/reception-hall')
            ->assertOk()
            ->assertJsonPath('data.slug', 'reception-hall');
    }

    public function test_gallery_categories_and_category_detail(): void
    {
        $this->createGalleryItem();

        $this->getJson('/api/gallery/categories')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'name',
                        'slug',
                        'image_count',
                    ],
                ],
            ]);

        $slug = $this->getJson('/api/gallery/categories')->json('data.0.slug');

        $this->getJson('/api/gallery/categories/'.$slug)
            ->assertOk()
            ->assertJsonPath('data.category.slug', $slug)
            ->assertJsonCount(1, 'data.items');
    }

    public function test_gallery_show_404_when_inactive(): void
    {
        $this->createGalleryItem(['status' => false]);

        $this->getJson('/api/gallery/reception-hall')->assertNotFound();
    }

    public function test_gallery_omits_items_without_a_public_preview(): void
    {
        $this->createGalleryItem();
        $this->createGalleryItem([
            'title' => 'Studio leftover',
            'slug' => 'studio-leftover',
            'image' => 'studio/uploads/qa-a.png',
            'thumbnail' => null,
        ]);

        $this->getJson('/api/gallery')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'reception-hall');

        $this->getJson('/api/gallery/studio-leftover')->assertNotFound();

        $slug = $this->getJson('/api/gallery/categories')->json('data.0.slug');
        $this->getJson('/api/gallery/categories/'.$slug)
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.slug', 'reception-hall');
    }
}

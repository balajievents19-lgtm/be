<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Support\CreatesWebsiteContent;
use Tests\TestCase;

class BlogApiTest extends TestCase
{
    use CreatesWebsiteContent;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->seedSettings();
    }

    public function test_blog_index_lists_published_posts(): void
    {
        $this->createBlogPost();
        $this->createBlogPost([
            'title' => 'Draft',
            'slug' => 'draft',
            'status' => false,
            'homepage_featured' => false,
        ]);
        $this->createBlogPost([
            'title' => 'Future',
            'slug' => 'future',
            'published_at' => now()->addDay(),
            'homepage_featured' => false,
        ]);

        $this->getJson('/api/blog')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'season-highlights');
    }

    public function test_blog_show_returns_content(): void
    {
        $this->createBlogPost([
            'seo_title' => 'Season SEO',
        ]);

        $this->getJson('/api/blog/season-highlights')
            ->assertOk()
            ->assertJsonPath('data.slug', 'season-highlights')
            ->assertJsonPath('data.content', '<p>Full post content.</p>');
    }

    public function test_blog_show_404_for_unpublished(): void
    {
        $this->createBlogPost(['status' => false]);

        $this->getJson('/api/blog/season-highlights')->assertNotFound();
    }
}

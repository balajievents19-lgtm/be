<?php

namespace Tests\Feature\Api;

use App\Enums\TestimonialType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Support\CreatesWebsiteContent;
use Tests\TestCase;

class HomeApiTest extends TestCase
{
    use CreatesWebsiteContent;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->seedSettings();
    }

    public function test_home_returns_expected_json_structure(): void
    {
        $this->createService();
        $this->createEventOverview();
        $this->createGalleryItem();
        $this->createTestimonial();
        $this->createTestimonial([
            'type' => TestimonialType::SuccessStory,
            'name' => 'Success Client',
            'sort_order' => 2,
        ]);
        $this->createBlogPost();
        $this->createFaq();

        $response = $this->getJson('/api/home');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'settings' => [
                        'company' => ['name'],
                        'brand',
                        'contact',
                        'seo',
                    ],
                    'hero',
                    'featured_services',
                    'events_overview',
                    'featured_gallery',
                    'gallery_categories',
                    'external_media',
                    'event_types',
                    'testimonials',
                    'success_stories',
                    'featured_blog',
                    'featured_faqs',
                    'faq_schema',
                    'statistics',
                    'cta_sections',
                    'team_members',
                    'office_locations',
                    'google_reviews',
                    'sections' => [
                        'slider',
                        'about',
                        'services',
                        'gallery',
                        'packages',
                        'testimonials',
                        'blog',
                        'faq',
                        'contact',
                    ],
                ],
            ]);

        $response->assertJsonPath('data.sections.faq', false);
        $response->assertJsonPath('data.sections.blog', true);
        $response->assertJsonPath('data.sections.slider', true);
        $response->assertJsonPath('data.featured_gallery', []);
        $response->assertJsonPath('data.team_members', []);
        $this->assertLessThanOrEqual(4, count($response->json('data.gallery_categories')));
        $this->assertSame('weddings', $response->json('data.gallery_categories.0.slug'));
        $this->assertSame(
            '/storage/services/wedding.jpg',
            $response->json('data.featured_services.0.featured_image')
        );
    }

    public function test_home_latest_news_uses_published_blog_posts_not_only_featured(): void
    {
        $this->createBlogPost([
            'title' => 'Published news',
            'slug' => 'published-news',
            'homepage_featured' => false,
            'status' => true,
            'published_at' => now()->subHour(),
        ]);
        $this->createBlogPost([
            'title' => 'Draft news',
            'slug' => 'draft-news',
            'homepage_featured' => true,
            'status' => false,
            'published_at' => now()->subHour(),
        ]);
        $this->createBlogPost([
            'title' => 'Future news',
            'slug' => 'future-news',
            'homepage_featured' => true,
            'status' => true,
            'published_at' => now()->addDay(),
        ]);

        $response = $this->getJson('/api/home');

        $response->assertOk();
        $slugs = collect($response->json('data.featured_blog'))->pluck('slug');
        $this->assertTrue($slugs->contains('published-news'));
        $this->assertFalse($slugs->contains('draft-news'));
        $this->assertFalse($slugs->contains('future-news'));
    }

    public function test_home_section_toggles_are_reflected_in_api(): void
    {
        $faq = \App\Models\HomepageSection::query()->where('section_key', 'faq')->firstOrFail();
        $faq->is_active = true;
        $faq->save();

        $blog = \App\Models\HomepageSection::query()->where('section_key', 'blog')->firstOrFail();
        $blog->is_active = false;
        $blog->save();

        Cache::flush();

        $response = $this->getJson('/api/home');

        $response->assertOk()
            ->assertJsonPath('data.sections.faq', true)
            ->assertJsonPath('data.sections.blog', false)
            ->assertJsonPath('data.sections.slider', true);
    }

    public function test_home_excludes_inactive_modules(): void
    {
        $this->createService(['status' => false, 'slug' => 'inactive-service']);
        $this->createService(['name' => 'Active Service', 'slug' => 'active-service']);

        $response = $this->getJson('/api/home');

        $response->assertOk();
        $slugs = collect($response->json('data.featured_services'))->pluck('slug');
        $this->assertTrue($slugs->contains('active-service'));
        $this->assertFalse($slugs->contains('inactive-service'));
    }
}

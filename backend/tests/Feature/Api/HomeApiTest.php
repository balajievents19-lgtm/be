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
                    'testimonials',
                    'success_stories',
                    'featured_blog',
                    'featured_faqs',
                    'faq_schema',
                    'statistics',
                    'cta_sections',
                    'team_members',
                    'office_locations',
                ],
            ]);
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

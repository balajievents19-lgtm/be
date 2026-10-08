<?php

namespace Tests\Feature\Api;

use App\Enums\TestimonialType;
use App\Models\Setting;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\Support\CreatesWebsiteContent;
use Tests\TestCase;

class FeaturedCustomerReviewsTest extends TestCase
{
    use CreatesWebsiteContent;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->seedSettings();
    }

    public function test_home_returns_at_most_four_featured_customer_reviews_in_order(): void
    {
        foreach ([4, 3, 2, 1] as $order) {
            $this->createTestimonial([
                'name' => 'Guest '.$order,
                'quote' => 'Quote '.$order,
                'google_reviews_featured' => true,
                'sort_order' => $order,
            ]);
        }

        $extra = $this->createTestimonial([
            'name' => 'Guest 5',
            'quote' => 'Quote 5',
            'google_reviews_featured' => false,
            'sort_order' => 5,
        ]);
        Testimonial::query()->whereKey($extra->id)->update(['google_reviews_featured' => true]);

        $this->createTestimonial([
            'name' => 'Not featured',
            'quote' => 'Hidden from Google section.',
            'google_reviews_featured' => false,
            'sort_order' => 0,
        ]);

        $response = $this->getJson('/api/home');
        $response->assertOk();

        $featured = $response->json('data.featured_customer_reviews');
        $this->assertIsArray($featured);
        $this->assertCount(4, $featured);
        $this->assertSame(['Guest 1', 'Guest 2', 'Guest 3', 'Guest 4'], array_column($featured, 'name'));
        $this->assertNotContains('Guest 5', array_column($featured, 'name'));
        $this->assertNotContains('Not featured', array_column($featured, 'name'));
    }

    public function test_cannot_feature_more_than_four_published_client_reviews(): void
    {
        for ($i = 1; $i <= 4; $i++) {
            $this->createTestimonial([
                'name' => 'Featured '.$i,
                'quote' => 'Quote '.$i,
                'google_reviews_featured' => true,
                'sort_order' => $i,
            ]);
        }

        $this->expectException(ValidationException::class);

        Testimonial::query()->create([
            'type' => TestimonialType::ClientSays,
            'name' => 'Fifth',
            'quote' => 'Should fail.',
            'status' => true,
            'google_reviews_featured' => true,
            'sort_order' => 9,
        ]);
    }

    public function test_google_reviews_rating_is_not_replaced_by_featured_testimonials(): void
    {
        config(['services.google_places.api_key' => 'test-places-key']);
        Setting::query()->update([
            'google_place_id' => 'ChIJ_test_place',
            'google_reviews_url' => 'https://maps.google.com/?cid=1',
        ]);

        $this->createTestimonial([
            'name' => 'Guest',
            'quote' => 'Owner-approved review.',
            'rating' => 5,
            'google_reviews_featured' => true,
        ]);

        Http::fake([
            'places.googleapis.com/*' => Http::response([
                'rating' => 4.9,
                'userRatingCount' => 25,
                'googleMapsUri' => 'https://maps.google.com/?cid=9',
            ], 200),
        ]);

        $this->getJson('/api/google-reviews')
            ->assertOk()
            ->assertJsonPath('data.rating', 4.9)
            ->assertJsonPath('data.review_count', 25)
            ->assertJsonPath('data.reviews', [])
            ->assertJsonPath('data.maps_url', 'https://maps.google.com/?cid=1');

        $home = $this->getJson('/api/home')->assertOk();
        $home->assertJsonPath('data.google_reviews.rating', 4.9);
        $home->assertJsonPath('data.google_reviews.review_count', 25);
        $this->assertSame('Guest', $home->json('data.featured_customer_reviews.0.name'));
    }
}

<?php

namespace Tests\Feature\Api;

use App\Models\Setting;
use App\Services\Google\GoogleReviewsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\Support\CreatesWebsiteContent;
use Tests\TestCase;

class GoogleReviewsApiTest extends TestCase
{
    use CreatesWebsiteContent;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->seedSettings();
    }

    public function test_google_reviews_are_inactive_without_credentials(): void
    {
        config([
            'services.google_places.api_key' => '',
            'services.google_places.place_id' => '',
        ]);

        $this->getJson('/api/google-reviews')
            ->assertOk()
            ->assertJsonPath('data.configured', false)
            ->assertJsonPath('data.reviews', []);
    }

    public function test_google_reviews_return_live_payload_when_places_succeeds(): void
    {
        config(['services.google_places.api_key' => 'test-places-key']);

        Setting::query()->update([
            'google_place_id' => 'ChIJ_test_place',
            'google_reviews_url' => 'https://maps.google.com/?cid=1',
        ]);

        Http::fake([
            'places.googleapis.com/*' => Http::response([
                'id' => 'places/ChIJ_test_place',
                'rating' => 4.8,
                'userRatingCount' => 42,
                'googleMapsUri' => 'https://maps.google.com/?cid=1',
                'reviews' => [
                    [
                        'rating' => 5,
                        'text' => ['text' => 'Beautiful wedding décor.'],
                        'publishTime' => '2026-01-02T00:00:00Z',
                        'relativePublishTimeDescription' => '8 months ago',
                        'authorAttribution' => [
                            'displayName' => 'Asha K.',
                            'uri' => 'https://maps.google.com/reviews/1',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $this->getJson('/api/google-reviews')
            ->assertOk()
            ->assertJsonPath('data.configured', true)
            ->assertJsonPath('data.rating', 4.8)
            ->assertJsonPath('data.review_count', 42)
            ->assertJsonPath('data.reviews.0.author_name', 'Asha K.')
            ->assertJsonPath('data.reviews.0.text', 'Beautiful wedding décor.');

        Http::assertSentCount(1);

        $this->getJson('/api/google-reviews')->assertOk();
        Http::assertSentCount(1);
    }

    public function test_google_reviews_do_not_fabricate_on_api_failure(): void
    {
        config(['services.google_places.api_key' => 'test-places-key']);
        Setting::query()->update(['google_place_id' => 'ChIJ_fail']);

        Http::fake([
            'places.googleapis.com/*' => Http::response(['error' => 'denied'], 403),
        ]);

        $this->getJson('/api/google-reviews')
            ->assertOk()
            ->assertJsonPath('data.configured', true)
            ->assertJsonPath('data.reviews', [])
            ->assertJsonPath('data.error', 'Google reviews are temporarily unavailable.');
    }

    public function test_admin_status_never_exposes_api_key(): void
    {
        config(['services.google_places.api_key' => 'super-secret-key']);

        $status = app(GoogleReviewsService::class)->status();

        $encoded = json_encode($status);
        $this->assertIsString($encoded);
        $this->assertStringNotContainsString('super-secret-key', $encoded);
        $this->assertTrue($status['places_api_key_configured']);
    }
}

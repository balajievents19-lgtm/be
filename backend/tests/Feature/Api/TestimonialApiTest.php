<?php

namespace Tests\Feature\Api;

use App\Enums\TestimonialType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Support\CreatesWebsiteContent;
use Tests\TestCase;

class TestimonialApiTest extends TestCase
{
    use CreatesWebsiteContent;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->seedSettings();
    }

    public function test_testimonials_index_lists_active_items(): void
    {
        $this->createTestimonial([
            'rating' => 5,
            'video_url' => 'https://www.youtube.com/watch?v=demo',
        ]);
        $this->createTestimonial([
            'type' => TestimonialType::SuccessStory,
            'name' => 'Story Client',
            'sort_order' => 2,
            'rating' => 4,
        ]);
        $this->createTestimonial([
            'name' => 'Hidden',
            'status' => false,
            'sort_order' => 3,
        ]);

        $response = $this->getJson('/api/testimonials');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'type',
                    'name',
                    'quote',
                    'body',
                    'avatar',
                    'image',
                    'rating',
                    'video_url',
                ]],
            ])
            ->assertJsonPath('data.0.rating', 5)
            ->assertJsonPath('data.0.video_url', 'https://www.youtube.com/watch?v=demo');

        $types = collect($response->json('data'))->pluck('type');
        $this->assertTrue($types->contains('client_says'));
        $this->assertTrue($types->contains('success_story'));
    }
}

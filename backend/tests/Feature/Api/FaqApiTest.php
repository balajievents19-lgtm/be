<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Support\CreatesWebsiteContent;
use Tests\TestCase;

class FaqApiTest extends TestCase
{
    use CreatesWebsiteContent;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->seedSettings();
    }

    public function test_faqs_index_includes_schema(): void
    {
        $this->createFaq();

        $this->getJson('/api/faqs')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'how-do-i-book')
            ->assertJsonStructure([
                'data',
                'schema' => ['@context', '@type', 'mainEntity'],
            ]);
    }

    public function test_faqs_show_returns_item_with_schema(): void
    {
        $this->createFaq();

        $this->getJson('/api/faqs/how-do-i-book')
            ->assertOk()
            ->assertJsonPath('data.slug', 'how-do-i-book')
            ->assertJsonStructure([
                'data' => ['id', 'question', 'answer', 'seo'],
                'schema',
            ]);
    }

    public function test_faqs_index_still_works_when_homepage_faq_section_is_off(): void
    {
        $this->createFaq();

        $this->assertFalse(
            (bool) \App\Models\HomepageSection::query()->where('section_key', 'faq')->value('is_active')
        );

        $this->getJson('/api/faqs')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
}

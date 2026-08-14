<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Support\CreatesWebsiteContent;
use Tests\TestCase;

class EventOverviewApiTest extends TestCase
{
    use CreatesWebsiteContent;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->seedSettings();
    }

    public function test_event_overviews_index_lists_active_items(): void
    {
        $this->createEventOverview();
        $this->createEventOverview([
            'title' => 'Inactive',
            'image' => 'events/inactive.jpg',
            'status' => false,
            'sort_order' => 2,
        ]);

        $this->getJson('/api/event-overviews')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Corporate Events')
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'title',
                    'caption',
                    'description',
                    'image',
                    'link_url',
                    'sort_order',
                ]],
            ]);
    }
}

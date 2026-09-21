<?php

namespace Tests\Feature\Api;

use App\Models\EventType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventTypeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_active_event_types_only_in_sort_order(): void
    {
        EventType::query()->create([
            'name' => 'Birthday',
            'slug' => 'birthday',
            'status' => true,
            'sort_order' => 2,
        ]);
        EventType::query()->create([
            'name' => 'Wedding',
            'slug' => 'wedding',
            'status' => true,
            'sort_order' => 1,
        ]);
        EventType::query()->create([
            'name' => 'Hidden',
            'slug' => 'hidden',
            'status' => false,
            'sort_order' => 0,
        ]);

        $this->getJson('/api/event-types')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Wedding')
            ->assertJsonPath('data.0.slug', 'wedding')
            ->assertJsonPath('data.1.name', 'Birthday')
            ->assertJsonMissingPath('data.0.status');
    }

    public function test_inactive_and_deleted_types_are_excluded(): void
    {
        $active = EventType::query()->create([
            'name' => 'Reception',
            'slug' => 'reception',
            'status' => true,
            'sort_order' => 1,
        ]);
        EventType::query()->create([
            'name' => 'Off',
            'slug' => 'off',
            'status' => false,
            'sort_order' => 2,
        ]);
        $active->delete();

        EventType::query()->create([
            'name' => 'Other',
            'slug' => 'other',
            'status' => true,
            'sort_order' => 3,
        ]);

        $this->getJson('/api/event-types')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'other');
    }
}

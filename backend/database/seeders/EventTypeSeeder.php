<?php

namespace Database\Seeders;

use App\Models\EventType;
use Illuminate\Database\Seeder;

class EventTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Wedding', 'slug' => 'wedding', 'sort_order' => 1],
            ['name' => 'Birthday', 'slug' => 'birthday', 'sort_order' => 2],
            ['name' => 'Corporate Event', 'slug' => 'corporate-event', 'sort_order' => 3],
            ['name' => 'Engagement', 'slug' => 'engagement', 'sort_order' => 4],
            ['name' => 'Reception', 'slug' => 'reception', 'sort_order' => 5],
            ['name' => 'Anniversary', 'slug' => 'anniversary', 'sort_order' => 6],
            ['name' => 'Other', 'slug' => 'other', 'sort_order' => 7],
        ];

        foreach ($types as $type) {
            EventType::query()->updateOrCreate(
                ['slug' => $type['slug']],
                [
                    'name' => $type['name'],
                    'status' => true,
                    'sort_order' => $type['sort_order'],
                    'icon' => null,
                ]
            );
        }
    }
}

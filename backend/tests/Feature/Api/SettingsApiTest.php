<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Support\CreatesWebsiteContent;
use Tests\TestCase;

class SettingsApiTest extends TestCase
{
    use CreatesWebsiteContent;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_settings_returns_singleton_payload(): void
    {
        $this->seedSettings([
            'email' => 'hello@balaji.test',
            'phone' => '+91 90000 00000',
        ]);

        $this->getJson('/api/settings')
            ->assertOk()
            ->assertJsonPath('data.company.name', 'Balaji Royal Events')
            ->assertJsonPath('data.contact.email', 'hello@balaji.test')
            ->assertJsonStructure([
                'data' => [
                    'company',
                    'about',
                    'brand',
                    'contact',
                    'social',
                    'seo',
                    'footer',
                ],
            ]);
    }

    public function test_settings_rewrites_outdated_company_name(): void
    {
        $this->seedSettings([
            'company_name' => 'Balaji Events',
        ]);

        $this->getJson('/api/settings')
            ->assertOk()
            ->assertJsonPath('data.company.name', 'Balaji Royal Events');
    }
}

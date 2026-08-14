<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use App\Support\Rbac\AdminModules;
use Database\Seeders\RBACSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\Support\CreatesWebsiteContent;
use Tests\TestCase;

class ProductionSecurityTest extends TestCase
{
    use CreatesWebsiteContent;
    use RefreshDatabase;

    public function test_security_headers_are_present_on_public_responses(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_health_endpoint_is_available_without_secrets(): void
    {
        $response = $this->get('/up');

        $response->assertOk();
        $body = $response->getContent();

        $this->assertStringNotContainsString('DB_PASSWORD', $body);
        $this->assertStringNotContainsString('APP_KEY', $body);
        $this->assertStringNotContainsString('password', strtolower($body));
    }

    public function test_contact_api_response_does_not_leak_internal_fields(): void
    {
        $response = $this->postJson('/api/contact', [
            'name' => 'Safe Lead',
            'mobile' => '9000000111',
            'email' => 'safe.lead@example.com',
            'message' => 'Need wedding planning.',
            'website' => '',
            'admin_notes' => 'should-not-persist',
            'status' => 'won',
            'priority' => 'high',
            'password' => 'secret',
        ])->assertCreated();

        $response->assertJsonMissingPath('data.admin_notes');
        $response->assertJsonMissingPath('data.password');
        $response->assertJsonMissingPath('data.remember_token');
        $response->assertJsonMissingPath('data.ip_address');
        $response->assertJsonMissingPath('data.user_agent');
        $response->assertJsonMissingPath('data.assigned_to');
        $response->assertJsonMissingPath('data.status');
        $response->assertJsonMissingPath('data.priority');
        $response->assertJsonPath('data.name', 'Safe Lead');
    }

    public function test_newsletter_api_response_does_not_leak_ip(): void
    {
        $this->postJson('/api/newsletter', [
            'email' => 'safe.newsletter@example.com',
            'website' => '',
        ])
            ->assertCreated()
            ->assertJsonMissingPath('data.ip_address')
            ->assertJsonMissingPath('data.password')
            ->assertJsonPath('data.email', 'safe.newsletter@example.com');
    }

    public function test_production_api_errors_do_not_expose_stack_traces(): void
    {
        Config::set('app.debug', false);

        $response = $this->getJson('/api/services/this-slug-does-not-exist-xyz');

        $response->assertNotFound();
        $body = $response->getContent();

        $this->assertStringNotContainsString('stack', strtolower($body));
        $this->assertStringNotContainsString('C:\\', $body);
        $this->assertStringNotContainsString('/var/www', $body);
        $this->assertStringNotContainsString('SQLSTATE', $body);
    }

    public function test_settings_api_does_not_expose_internal_seo_secrets_beyond_public_fields(): void
    {
        $this->seedSettings([
            'google_analytics_id' => 'G-TEST',
            'meta_title' => 'Public title',
        ]);

        $response = $this->getJson('/api/settings')->assertOk();

        $response->assertJsonPath('data.seo.meta_title', 'Public title');
        $response->assertJsonMissingPath('data.password');
        $response->assertJsonMissingPath('data.remember_token');
    }

    public function test_admin_still_requires_authentication(): void
    {
        $this->get('/admin')->assertRedirect();
    }

    public function test_rbac_and_users_protections_remain(): void
    {
        $this->seed(RBACSeeder::class);

        $content = User::factory()->create(['email' => 'prod.content@balaji.test']);
        $content->assignRole(AdminModules::ROLE_CONTENT_MANAGER);

        $this->actingAs($content)
            ->get(UserResource::getUrl('index'))
            ->assertForbidden();

        $this->assertFalse($content->can('users.view'));
        $this->assertFalse($content->can('assignRoles'));
    }
}

<?php

namespace Tests\Feature\Customer;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOAuthConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_oauth_redirect_returns_configuration_pending_without_credentials(): void
    {
        config([
            'services.google.client_id' => '',
            'services.google.client_secret' => '',
            'services.google.redirect' => '',
        ]);

        $this->getJson('/api/customer/oauth/google/redirect')
            ->assertStatus(503)
            ->assertJsonPath('status', 'configuration_pending');
    }

    public function test_unsupported_provider_is_404(): void
    {
        $this->getJson('/api/customer/oauth/twitter/redirect')
            ->assertNotFound();
    }
}

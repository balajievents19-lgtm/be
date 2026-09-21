<?php

namespace Tests\Feature\Customer;

use App\Models\Customer;
use App\Models\CustomerSocialAccount;
use App\Services\Customer\CustomerOAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class CustomerOAuthFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'sanctum.stateful' => ['localhost', 'localhost:3000', '127.0.0.1'],
            'app.url' => 'http://localhost:8000',
        ]);
    }

    public function test_google_redirect_is_pending_without_credentials(): void
    {
        config([
            'services.google.client_id' => '',
            'services.google.client_secret' => '',
        ]);

        $this->get('/api/customer/oauth/google/redirect')
            ->assertStatus(503)
            ->assertJsonPath('status', 'configuration_pending');
    }

    public function test_facebook_redirect_is_pending_without_credentials(): void
    {
        config([
            'services.facebook.client_id' => '',
            'services.facebook.client_secret' => '',
        ]);

        $this->get('/api/customer/oauth/facebook/redirect')
            ->assertStatus(503)
            ->assertJsonPath('status', 'configuration_pending');
    }

    public function test_google_redirect_sends_browser_to_google(): void
    {
        $this->configureGoogle();

        $response = $this->get('/api/customer/oauth/google/redirect');

        $response->assertRedirect();
        $location = (string) $response->headers->get('Location');
        $this->assertNotSame('', $location);
        $this->assertTrue(
            str_contains($location, 'accounts.google.com') || str_contains($location, 'google.com/o/oauth2'),
            $location
        );
        $this->assertStringContainsString('client_id=test-google-client-id', $location);
    }

    public function test_facebook_redirect_sends_browser_to_facebook(): void
    {
        $this->configureFacebook();

        $response = $this->get('/api/customer/oauth/facebook/redirect');

        $response->assertRedirect();
        $location = (string) $response->headers->get('Location');
        $this->assertTrue(
            str_contains($location, 'facebook.com'),
            $location
        );
        $this->assertStringContainsString('client_id=test-facebook-client-id', $location);
    }

    public function test_google_callback_creates_and_logs_in_customer(): void
    {
        $this->configureGoogle();
        $this->fakeSocialiteUser('google', 'google-99', 'oauth.google@example.com', 'Google User');

        $this->get('/api/customer/oauth/google/callback')
            ->assertRedirect();

        $this->assertAuthenticated('customer');
        $this->assertDatabaseHas('customers', ['email' => 'oauth.google@example.com']);
        $this->assertDatabaseHas('customer_social_accounts', [
            'provider' => 'google',
            'provider_user_id' => 'google-99',
        ]);
    }

    public function test_facebook_callback_creates_and_logs_in_customer(): void
    {
        $this->configureFacebook();
        $this->fakeSocialiteUser('facebook', 'fb-99', 'oauth.facebook@example.com', 'Facebook User');

        $this->get('/api/customer/oauth/facebook/callback')
            ->assertRedirect();

        $this->assertAuthenticated('customer');
        $this->assertDatabaseHas('customers', ['email' => 'oauth.facebook@example.com']);
        $this->assertDatabaseHas('customer_social_accounts', [
            'provider' => 'facebook',
            'provider_user_id' => 'fb-99',
        ]);
    }

    public function test_existing_social_account_logs_into_same_customer(): void
    {
        $this->configureGoogle();
        $customer = Customer::factory()->create([
            'email' => 'existing.social@example.com',
            'email_verified_at' => now(),
        ]);
        CustomerSocialAccount::query()->create([
            'customer_id' => $customer->id,
            'provider' => 'google',
            'provider_user_id' => 'google-existing',
        ]);

        $this->fakeSocialiteUser('google', 'google-existing', 'existing.social@example.com', 'Existing');

        $this->get('/api/customer/oauth/google/callback')->assertRedirect();

        $this->assertAuthenticatedAs($customer->fresh(), 'customer');
        $this->assertSame(1, Customer::query()->where('email', 'existing.social@example.com')->count());
    }

    public function test_duplicate_social_identity_cannot_be_attached_twice(): void
    {
        $customer = Customer::factory()->create(['email_verified_at' => now()]);
        CustomerSocialAccount::query()->create([
            'customer_id' => $customer->id,
            'provider' => 'google',
            'provider_user_id' => 'google-unique',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        CustomerSocialAccount::query()->create([
            'customer_id' => $customer->id,
            'provider' => 'google',
            'provider_user_id' => 'google-unique',
        ]);
    }

    public function test_verified_email_is_linked_instead_of_duplicated(): void
    {
        $existing = Customer::factory()->create([
            'email' => 'linked@example.com',
            'email_verified_at' => now(),
        ]);

        $social = Mockery::mock(SocialiteUser::class);
        $social->shouldReceive('getId')->andReturn('google-link');
        $social->shouldReceive('getEmail')->andReturn('linked@example.com');
        $social->shouldReceive('getName')->andReturn('Linked');
        $social->shouldReceive('getNickname')->andReturn(null);
        $social->shouldReceive('getAvatar')->andReturn(null);

        $customer = app(CustomerOAuthService::class)->findOrCreateFromProvider('google', $social);

        $this->assertSame($existing->id, $customer->id);
        $this->assertSame(1, Customer::query()->where('email', 'linked@example.com')->count());
    }

    public function test_google_callback_without_credentials_redirects_not_configured(): void
    {
        config([
            'services.google.client_id' => '',
            'services.google.client_secret' => '',
            'seo.site_url' => 'http://localhost:3000',
        ]);

        $this->get('/api/customer/oauth/google/callback')
            ->assertRedirect('http://localhost:3000/login?oauth=not_configured&provider=google');
    }

    public function test_facebook_callback_without_credentials_redirects_not_configured(): void
    {
        config([
            'services.facebook.client_id' => '',
            'services.facebook.client_secret' => '',
            'seo.site_url' => 'http://localhost:3000',
        ]);

        $this->get('/api/customer/oauth/facebook/callback')
            ->assertRedirect('http://localhost:3000/login?oauth=not_configured&provider=facebook');
    }

    private function configureGoogle(): void
    {
        config([
            'services.google.client_id' => 'test-google-client-id',
            'services.google.client_secret' => 'test-google-secret',
            'services.google.redirect' => 'http://localhost:8000/api/customer/oauth/google/callback',
        ]);
    }

    private function configureFacebook(): void
    {
        config([
            'services.facebook.client_id' => 'test-facebook-client-id',
            'services.facebook.client_secret' => 'test-facebook-secret',
            'services.facebook.redirect' => 'http://localhost:8000/api/customer/oauth/facebook/callback',
        ]);
    }

    private function fakeSocialiteUser(string $provider, string $id, string $email, string $name): void
    {
        $user = Mockery::mock(SocialiteUser::class);
        $user->shouldReceive('getId')->andReturn($id);
        $user->shouldReceive('getEmail')->andReturn($email);
        $user->shouldReceive('getName')->andReturn($name);
        $user->shouldReceive('getNickname')->andReturn(null);
        $user->shouldReceive('getAvatar')->andReturn(null);

        $driver = Mockery::mock();
        $driver->shouldReceive('stateless')->with(false)->andReturnSelf();
        $driver->shouldReceive('user')->andReturn($user);

        Socialite::shouldReceive('driver')->with($provider)->andReturn($driver);
    }
}

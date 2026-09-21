<?php

namespace Tests\Feature\Customer;

use App\Models\Customer;
use App\Services\Customer\CustomerOAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class CustomerOAuthServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_customer_from_google_identity(): void
    {
        $social = Mockery::mock(SocialiteUser::class);
        $social->shouldReceive('getId')->andReturn('google-1');
        $social->shouldReceive('getEmail')->andReturn('new@example.com');
        $social->shouldReceive('getName')->andReturn('New Customer');
        $social->shouldReceive('getNickname')->andReturn(null);
        $social->shouldReceive('getAvatar')->andReturn(null);

        $customer = app(CustomerOAuthService::class)->findOrCreateFromProvider('google', $social);

        $this->assertSame('new@example.com', $customer->email);
        $this->assertNotNull($customer->email_verified_at);
        $this->assertNull($customer->mobile_verified_at);
        $this->assertDatabaseHas('customer_social_accounts', [
            'provider' => 'google',
            'provider_user_id' => 'google-1',
            'customer_id' => $customer->id,
        ]);
    }

    public function test_does_not_merge_unverified_email_accounts(): void
    {
        Customer::factory()->create([
            'email' => 'taken@example.com',
            'email_verified_at' => null,
        ]);

        $social = Mockery::mock(SocialiteUser::class);
        $social->shouldReceive('getId')->andReturn('google-2');
        $social->shouldReceive('getEmail')->andReturn('taken@example.com');
        $social->shouldReceive('getName')->andReturn('Taken');
        $social->shouldReceive('getNickname')->andReturn(null);
        $social->shouldReceive('getAvatar')->andReturn(null);

        $this->expectException(\RuntimeException::class);
        app(CustomerOAuthService::class)->findOrCreateFromProvider('google', $social);
    }

    public function test_links_to_verified_email_account(): void
    {
        $existing = Customer::factory()->create([
            'email' => 'verified@example.com',
            'email_verified_at' => now(),
        ]);

        $social = Mockery::mock(SocialiteUser::class);
        $social->shouldReceive('getId')->andReturn('fb-1');
        $social->shouldReceive('getEmail')->andReturn('verified@example.com');
        $social->shouldReceive('getName')->andReturn('Verified');
        $social->shouldReceive('getNickname')->andReturn(null);
        $social->shouldReceive('getAvatar')->andReturn(null);

        $customer = app(CustomerOAuthService::class)->findOrCreateFromProvider('facebook', $social);

        $this->assertSame($existing->id, $customer->id);
        $this->assertDatabaseHas('customer_social_accounts', [
            'provider' => 'facebook',
            'provider_user_id' => 'fb-1',
            'customer_id' => $existing->id,
        ]);
    }
}

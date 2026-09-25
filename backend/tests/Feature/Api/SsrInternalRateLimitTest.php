<?php

namespace Tests\Feature\Api;

use App\Support\SsrInternalAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class SsrInternalRateLimitTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'test-ssr-internal-secret-not-for-production';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('ssr.internal_secret', self::SECRET);
        Config::set('ssr.rate_limit_per_minute', 300);
        Config::set('ssr.header', 'X-SSR-Secret');

        RateLimiter::clear('127.0.0.1');
        RateLimiter::clear('ssr:127.0.0.1');
        RateLimiter::clear('contact:127.0.0.1');
        RateLimiter::clear('newsletter:127.0.0.1');
    }

    public function test_public_get_is_throttled_after_sixty_requests(): void
    {
        for ($i = 0; $i < 60; $i++) {
            $this->getJson('/api/settings')->assertOk();
        }

        $this->getJson('/api/settings')->assertStatus(429);
    }

    public function test_trusted_ssr_get_uses_separate_finite_limiter(): void
    {
        $headers = [SsrInternalAuth::headerName() => self::SECRET];

        for ($i = 0; $i < 61; $i++) {
            $this->withHeaders($headers)
                ->getJson('/api/settings')
                ->assertOk();
        }
    }

    public function test_wrong_ssr_secret_stays_on_public_limiter(): void
    {
        for ($i = 0; $i < 60; $i++) {
            $this->withHeaders([SsrInternalAuth::headerName() => 'wrong-secret'])
                ->getJson('/api/settings')
                ->assertOk();
        }

        $this->withHeaders([SsrInternalAuth::headerName() => 'wrong-secret'])
            ->getJson('/api/settings')
            ->assertStatus(429);
    }

    public function test_ssr_secret_does_not_bypass_contact_authentication(): void
    {
        $this->withHeaders([SsrInternalAuth::headerName() => self::SECRET])
            ->postJson('/api/contact', [
                'name' => 'SSR Probe',
                'mobile' => '9876543210',
                'email' => 'ssr@example.com',
                'message' => 'Should still require a verified customer.',
                'website' => '',
            ])
            ->assertUnauthorized()
            ->assertJsonPath('code', 'customer_auth_required');
    }

    public function test_ssr_secret_does_not_exempt_contact_post_throttle(): void
    {
        RateLimiter::clear('contact:127.0.0.1');

        $payload = [
            'name' => 'SSR Probe',
            'mobile' => '9876543210',
            'email' => 'ssr@example.com',
            'message' => 'Should still hit contact throttle.',
            'website' => '',
        ];

        $customer = \App\Models\Customer::factory()->create([
            'name' => 'SSR Probe',
            'email' => 'ssr@example.com',
            'phone' => '9876543210',
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($customer, 'customer')
                ->withHeaders([SsrInternalAuth::headerName() => self::SECRET])
                ->postJson('/api/contact', array_merge($payload, [
                    'message' => 'Should still hit contact throttle. '.$i,
                ]))
                ->assertCreated();
        }

        $this->actingAs($customer, 'customer')
            ->withHeaders([SsrInternalAuth::headerName() => self::SECRET])
            ->postJson('/api/contact', $payload)
            ->assertStatus(429);
    }

    public function test_ssr_secret_does_not_exempt_newsletter_post_throttle(): void
    {
        RateLimiter::clear('newsletter:127.0.0.1');

        $payload = [
            'email' => 'ssr-probe@example.com',
            'website' => '',
        ];

        for ($i = 0; $i < 5; $i++) {
            $this->withHeaders([SsrInternalAuth::headerName() => self::SECRET])
                ->postJson('/api/newsletter', array_merge($payload, [
                    'email' => "ssr-probe-{$i}@example.com",
                ]))
                ->assertCreated();
        }

        $this->withHeaders([SsrInternalAuth::headerName() => self::SECRET])
            ->postJson('/api/newsletter', $payload)
            ->assertStatus(429);
    }

    public function test_empty_secret_never_trusts_header(): void
    {
        Config::set('ssr.internal_secret', '');

        $this->assertFalse(SsrInternalAuth::isTrustedRead(
            request()->create('/api/settings', 'GET', [], [], [], [
                'HTTP_X_SSR_SECRET' => 'anything',
            ])
        ));
    }
}

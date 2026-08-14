<?php

namespace Tests\Feature\Api;

use App\Models\NewsletterSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class NewsletterApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@example.com',
            'website' => '',
        ], $overrides);
    }

    public function test_newsletter_store_creates_subscriber(): void
    {
        $this->postJson('/api/newsletter', $this->validPayload())
            ->assertCreated()
            ->assertJsonPath('data.email', 'ada@example.com');

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'ada@example.com',
            'first_name' => 'Ada',
        ]);
    }

    public function test_newsletter_update_or_create_returns_200_on_resubscribe(): void
    {
        $this->postJson('/api/newsletter', $this->validPayload())->assertCreated();

        $this->postJson('/api/newsletter', $this->validPayload([
            'first_name' => 'Updated',
        ]))->assertOk()
            ->assertJsonPath('data.first_name', 'Updated');

        $this->assertSame(1, NewsletterSubscriber::query()->count());
    }

    public function test_newsletter_requires_valid_email(): void
    {
        $this->postJson('/api/newsletter', [
            'email' => 'not-an-email',
            'website' => '',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_newsletter_rejects_honeypot_spam(): void
    {
        $this->postJson('/api/newsletter', $this->validPayload([
            'website' => 'bot',
        ]))->assertStatus(422)
            ->assertJsonValidationErrors(['website']);
    }

    public function test_newsletter_is_rate_limited(): void
    {
        RateLimiter::clear('newsletter:'.'127.0.0.1');

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/newsletter', $this->validPayload([
                'email' => "user{$i}@example.com",
            ]))->assertSuccessful();
        }

        $this->postJson('/api/newsletter', $this->validPayload([
            'email' => 'user9@example.com',
        ]))->assertStatus(429);
    }
}

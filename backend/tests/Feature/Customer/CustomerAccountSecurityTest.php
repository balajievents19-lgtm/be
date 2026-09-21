<?php

namespace Tests\Feature\Customer;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CustomerAccountSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        config(['sanctum.stateful' => ['localhost', 'localhost:3000']]);
    }

    public function test_change_email_does_not_update_until_token_verified(): void
    {
        $customer = Customer::factory()->create([
            'email' => 'old@example.com',
            'password' => 'Password1!',
        ]);

        $this->actingAs($customer, 'customer')
            ->postJson('/api/customer/change-email', [
                'current_password' => 'Password1!',
                'email' => 'new@example.com',
            ])->assertOk();

        $this->assertSame('old@example.com', $customer->fresh()->email);
        $this->assertSame('new@example.com', $customer->fresh()->pending_email);

        $this->actingAs($customer, 'customer')
            ->postJson('/api/customer/verify-email', [
                'token' => 'invalid-token',
            ])->assertUnprocessable();
    }

    public function test_change_mobile_updates_after_password_confirmation(): void
    {
        $customer = Customer::factory()->create([
            'phone' => '9111111111',
            'password' => 'Password1!',
        ]);

        $this->actingAs($customer, 'customer')
            ->postJson('/api/customer/change-mobile', [
                'current_password' => 'Password1!',
                'phone' => '9222222222',
            ])->assertOk();

        $this->assertSame('9222222222', $customer->fresh()->phone);
    }
}

<?php

namespace Tests\Feature\Customer;

use App\Models\Customer;
use App\Models\User;
use App\Notifications\CustomerVerifyEmail;
use App\Services\Customer\EmailVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class CustomerAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        config(['sanctum.stateful' => ['localhost', 'localhost:3000', '127.0.0.1', '127.0.0.1:3000']]);
        \Illuminate\Support\Facades\RateLimiter::clear('customer-register:'.'127.0.0.1');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function spaPostJson(string $uri, array $data = [])
    {
        return $this->withHeaders([
            'Origin' => 'http://localhost:3000',
            'Referer' => 'http://localhost:3000/',
        ])->postJson($uri, $data);
    }

    public function test_register_validation_requires_core_fields(): void
    {
        $this->spaPostJson('/api/customer/register', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password', 'phone', 'terms']);
    }

    public function test_register_phone_must_be_exactly_ten_digits(): void
    {
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        $invalid = [
            '+919462577065',
            '94625 77065',
            '94625-77065',
            '946257706a',
            '946257706!',
            '94625770655',
            '919462577065',
        ];

        foreach ($invalid as $i => $phone) {
            \Illuminate\Support\Facades\RateLimiter::clear('customer-register:'.'127.0.0.1');
            $this->spaPostJson('/api/customer/register', [
                'name' => 'Phone User',
                'email' => "phone{$i}@example.com",
                'username' => "phone_user_{$i}",
                'password' => 'Password1!',
                'password_confirmation' => 'Password1!',
                'phone' => $phone,
                'terms' => true,
            ])->assertUnprocessable()->assertJsonValidationErrors(['phone']);
        }
    }

    public function test_register_accepts_ten_digits_starting_with_any_digit(): void
    {
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        foreach (['0462577065', '1462577065', '5462577065'] as $i => $phone) {
            $this->spaPostJson('/api/customer/register', [
                'name' => 'Any Digit',
                'email' => "start{$i}@example.com",
                'username' => "start_user_{$i}",
                'password' => 'Password1!',
                'password_confirmation' => 'Password1!',
                'phone' => $phone,
                'terms' => true,
            ])->assertCreated();

            $this->assertDatabaseHas('customers', [
                'email' => "start{$i}@example.com",
                'phone' => $phone,
            ]);
        }
    }

    public function test_customer_register_requires_email_verification_and_does_not_login(): void
    {
        $response = $this->spaPostJson('/api/customer/register', [
            'name' => 'Guest User',
            'email' => 'guest@example.com',
            'username' => 'guest_user',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'phone' => '9876543210',
            'terms' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.email', 'guest@example.com')
            ->assertJsonPath('verification.required', true)
            ->assertJsonPath('verification.sent', true)
            ->assertJsonMissingPath('data.password')
            ->assertJsonMissingPath('verification.code')
            ->assertJsonMissingPath('otp.code');

        $this->assertGuest('customer');
        $this->assertDatabaseHas('customers', [
            'email' => 'guest@example.com',
            'email_verified_at' => null,
        ]);
        $this->assertStringNotContainsString('"987654"', $response->getContent());
        Notification::assertSentTo(
            Customer::query()->where('email', 'guest@example.com')->first(),
            CustomerVerifyEmail::class
        );
    }

    public function test_email_verification_activates_account(): void
    {
        $this->spaPostJson('/api/customer/register', [
            'name' => 'Guest User',
            'email' => 'guest@example.com',
            'username' => 'guest_user',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'phone' => '9876543210',
            'terms' => true,
        ])->assertCreated();

        $code = app(EmailVerificationService::class)->lastCode('guest@example.com');
        $this->assertNotNull($code);
        $this->assertSame(6, strlen((string) $code));

        $this->spaPostJson('/api/customer/email/verify', [
            'email' => 'guest@example.com',
            'otp' => $code,
        ])->assertOk()->assertJsonPath('data.email_verified', true);

        $customer = Customer::query()->where('email', 'guest@example.com')->first();
        $this->assertNotNull($customer?->email_verified_at);
        $this->assertAuthenticatedAs($customer, 'customer');
    }

    public function test_invalid_and_limited_email_verification_attempts(): void
    {
        $this->spaPostJson('/api/customer/register', [
            'name' => 'Guest User',
            'email' => 'guest@example.com',
            'username' => 'guest_user',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'phone' => '9876543210',
            'terms' => true,
        ])->assertCreated();

        for ($i = 0; $i < 5; $i++) {
            $this->spaPostJson('/api/customer/email/verify', [
                'email' => 'guest@example.com',
                'otp' => '000000',
            ])->assertUnprocessable();
        }

        $code = app(EmailVerificationService::class)->lastCode('guest@example.com');

        $this->spaPostJson('/api/customer/email/verify', [
            'email' => 'guest@example.com',
            'otp' => $code,
        ])->assertUnprocessable();
    }

    public function test_expired_email_verification_is_rejected(): void
    {
        $this->spaPostJson('/api/customer/register', [
            'name' => 'Guest User',
            'email' => 'guest@example.com',
            'username' => 'guest_user',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'phone' => '9876543210',
            'terms' => true,
        ])->assertCreated();

        $this->travel(11)->minutes();

        $code = app(EmailVerificationService::class)->lastCode('guest@example.com');
        $this->spaPostJson('/api/customer/email/verify', [
            'email' => 'guest@example.com',
            'otp' => $code,
        ])->assertUnprocessable();

        $this->assertNull(Customer::query()->where('email', 'guest@example.com')->first()?->email_verified_at);
    }

    public function test_email_verification_resend_respects_cooldown_then_succeeds(): void
    {
        $this->spaPostJson('/api/customer/register', [
            'name' => 'Guest User',
            'email' => 'guest@example.com',
            'username' => 'guest_user',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'phone' => '9876543210',
            'terms' => true,
        ])->assertCreated();

        $this->spaPostJson('/api/customer/email/resend', [
            'email' => 'guest@example.com',
        ])->assertUnprocessable();

        $this->travel(61)->seconds();

        $this->spaPostJson('/api/customer/email/resend', [
            'email' => 'guest@example.com',
        ])->assertOk()->assertJsonPath('verification.sent', true);
    }

    public function test_email_verification_rate_limiting(): void
    {
        $this->spaPostJson('/api/customer/register', [
            'name' => 'Guest User',
            'email' => 'guest@example.com',
            'username' => 'guest_user',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'phone' => '9876543210',
            'terms' => true,
        ])->assertCreated();

        $this->travel(61)->seconds();

        for ($i = 0; $i < 8; $i++) {
            $this->spaPostJson('/api/customer/email/resend', [
                'email' => 'guest@example.com',
            ]);
        }

        $this->spaPostJson('/api/customer/email/resend', [
            'email' => 'guest@example.com',
        ])->assertStatus(429);
    }

    public function test_unverified_customer_cannot_login(): void
    {
        Customer::factory()->unverified()->create([
            'email' => 'pending@example.com',
            'password' => 'Password1!',
        ]);

        $this->spaPostJson('/api/customer/login', [
            'login' => 'pending@example.com',
            'password' => 'Password1!',
        ])->assertForbidden()->assertJsonPath('code', 'email_verification_required');

        $this->assertGuest('customer');
    }

    public function test_signed_email_verification_link_marks_verified(): void
    {
        $customer = Customer::factory()->unverified()->create([
            'email' => 'link@example.com',
        ]);

        $url = app(EmailVerificationService::class)->signedUrl($customer);
        $this->get($url)->assertRedirect();

        $this->assertNotNull($customer->fresh()->email_verified_at);
        $this->assertGuest('customer');
    }

    public function test_register_from_frontend_origin_does_not_csrf_mismatch(): void
    {
        $this->spaPostJson('/api/customer/register', [
            'name' => 'Csrf User',
            'email' => 'csrf@example.com',
            'username' => 'csrf_user',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'phone' => '9876500123',
            'terms' => true,
        ])->assertCreated()->assertJsonMissing(['message' => 'CSRF token mismatch.']);
    }

    public function test_duplicate_email_and_mobile_are_rejected(): void
    {
        Customer::factory()->create([
            'email' => 'dup@example.com',
            'username' => 'dup_user',
            'phone' => '9876543210',
        ]);

        $this->spaPostJson('/api/customer/register', [
            'name' => 'Other',
            'email' => 'dup@example.com',
            'username' => 'other_user',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'phone' => '91234567890',
            'terms' => true,
        ])->assertUnprocessable();

        $this->spaPostJson('/api/customer/register', [
            'name' => 'Other',
            'email' => 'other@example.com',
            'username' => 'other_user',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'phone' => '9876543210',
            'terms' => true,
        ])->assertUnprocessable();
    }

    public function test_customer_can_login_with_email_mobile_or_username_and_logout(): void
    {
        $customer = Customer::factory()->create([
            'email' => 'login@example.com',
            'username' => 'login_user',
            'phone' => '9876543210',
            'password' => 'Password1!',
        ]);

        $this->spaPostJson('/api/customer/login', [
            'login' => 'login@example.com',
            'password' => 'Password1!',
            'remember' => true,
        ])->assertOk()->assertJsonPath('data.id', $customer->id);

        $this->assertAuthenticatedAs($customer, 'customer');

        $this->withHeaders([
            'Origin' => 'http://localhost:3000',
            'Referer' => 'http://localhost:3000/',
        ])->postJson('/api/customer/logout')->assertOk();
        $this->assertGuest('customer');

        $this->spaPostJson('/api/customer/login', [
            'login' => '9876543210',
            'password' => 'Password1!',
        ])->assertOk();
    }

    public function test_invalid_login_is_generic(): void
    {
        Customer::factory()->create([
            'email' => 'login@example.com',
            'password' => 'Password1!',
        ]);

        $this->spaPostJson('/api/customer/login', [
            'login' => 'login@example.com',
            'password' => 'wrong-password',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['login']);
    }

    public function test_me_requires_customer_auth(): void
    {
        $this->getJson('/api/customer/me')->assertUnauthorized();

        $customer = Customer::factory()->create();
        $this->actingAs($customer, 'customer')
            ->getJson('/api/customer/me')
            ->assertOk()
            ->assertJsonPath('data.email', $customer->email);
    }

    public function test_forgot_and_reset_password(): void
    {
        $customer = Customer::factory()->create([
            'email' => 'reset@example.com',
            'password' => 'Password1!',
        ]);

        $this->spaPostJson('/api/customer/forgot-password', [
            'email' => 'reset@example.com',
        ])->assertOk();

        $this->spaPostJson('/api/customer/forgot-password', [
            'email' => 'missing@example.com',
        ])->assertOk();

        $token = Password::broker('customers')->createToken($customer);

        $this->spaPostJson('/api/customer/reset-password', [
            'email' => 'reset@example.com',
            'token' => $token,
            'password' => 'NewPassword1!',
            'password_confirmation' => 'NewPassword1!',
        ])->assertOk();

        $this->assertTrue(Hash::check('NewPassword1!', $customer->fresh()->password));
    }

    public function test_unverified_customer_cannot_download_gallery_original(): void
    {
        $customer = Customer::factory()->unverified()->create();

        $this->actingAs($customer, 'customer')
            ->getJson('/api/gallery/items/1/download')
            ->assertForbidden()
            ->assertJsonPath('code', 'email_verification_required');
    }

    public function test_change_password_requires_current_and_rejects_reuse(): void
    {
        $customer = Customer::factory()->create(['password' => 'Password1!']);

        $this->actingAs($customer, 'customer')
            ->postJson('/api/customer/change-password', [
                'current_password' => 'Password1!',
                'password' => 'Password1!',
                'password_confirmation' => 'Password1!',
            ])->assertUnprocessable();

        $this->actingAs($customer, 'customer')
            ->postJson('/api/customer/change-password', [
                'current_password' => 'Password1!',
                'password' => 'NewPassword1!',
                'password_confirmation' => 'NewPassword1!',
            ])->assertOk();

        $this->assertTrue(Hash::check('NewPassword1!', $customer->fresh()->password));
    }

    public function test_sessions_can_be_listed_and_others_cleared(): void
    {
        $customer = Customer::factory()->create(['password' => 'Password1!']);

        $this->spaPostJson('/api/customer/login', [
            'login' => $customer->email,
            'password' => 'Password1!',
        ])->assertOk();

        $this->withHeaders([
            'Origin' => 'http://localhost:3000',
            'Referer' => 'http://localhost:3000/',
        ])->getJson('/api/customer/sessions')
            ->assertOk()
            ->assertJsonMissingPath('data.0.laravel_session_id');

        $this->deleteJson('/api/customer/sessions/others')->assertOk();
    }

    public function test_instagram_is_not_offered(): void
    {
        $this->getJson('/api/customer/oauth/instagram/redirect')->assertNotFound();
        $this->getJson('/api/customer/oauth/providers')
            ->assertOk()
            ->assertJsonPath('data.instagram', false);
    }

    public function test_customer_cannot_access_filament_admin_as_customer_only(): void
    {
        $customer = Customer::factory()->create();

        $this->actingAs($customer, 'customer')
            ->get('/admin')
            ->assertRedirect();
    }

    public function test_admin_user_still_uses_web_guard_not_customer_table(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@balaji.test',
            'password' => 'Password1!',
        ]);

        $this->assertDatabaseMissing('customers', ['email' => 'admin@balaji.test']);
        $this->assertTrue(Hash::check('Password1!', $admin->password));
    }
}

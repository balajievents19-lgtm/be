<?php

namespace Tests\Feature\Api;

use App\Models\ContactInquiry;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceInquiryApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['sanctum.stateful' => ['localhost', 'localhost:3000', '127.0.0.1', '127.0.0.1:3000']]);
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Customer $customer, array $overrides = []): array
    {
        return array_merge([
            'name' => $customer->name,
            'email' => $customer->email,
            'mobile' => $customer->phone,
            'subject' => 'Service inquiry: Wedding Planning',
            'service_interested' => 'Wedding Planning',
            'event_date' => '2031-07-20',
            'source' => 'service_inquiry',
            'message' => 'We would like a quote.',
            'website' => '',
        ], $overrides);
    }

    private function postInquiry(array $data, ?Customer $as = null)
    {
        $request = $this->withHeaders([
            'Origin' => 'http://localhost:3000',
            'Referer' => 'http://localhost:3000/',
        ]);

        if ($as !== null) {
            $request = $request->actingAs($as, 'customer');
        }

        return $request->postJson('/api/contact', $data);
    }

    public function test_guest_cannot_submit_service_inquiry(): void
    {
        $this->postInquiry([
            'name' => 'Guest',
            'email' => 'guest@example.com',
            'mobile' => '9462577065',
            'source' => 'service_inquiry',
            'message' => 'Hello',
        ])->assertUnauthorized();

        $this->assertSame(0, ContactInquiry::query()->count());
    }

    public function test_unverified_customer_cannot_submit(): void
    {
        $customer = Customer::factory()->unverified()->create([
            'phone' => '9462577065',
        ]);

        $this->postInquiry($this->payload($customer), $customer)
            ->assertForbidden()
            ->assertJsonPath('code', 'email_verification_required');

        $this->assertSame(0, ContactInquiry::query()->count());
    }

    public function test_verified_customer_can_submit(): void
    {
        $customer = Customer::factory()->create([
            'name' => 'Verified Customer',
            'email' => 'verified@example.com',
            'phone' => '9462577065',
        ]);

        $this->postInquiry($this->payload($customer), $customer)
            ->assertCreated()
            ->assertJsonPath('data.customer_id', $customer->id)
            ->assertJsonPath('data.email', 'verified@example.com')
            ->assertJsonPath('data.mobile', '9462577065')
            ->assertJsonPath('data.source', 'service_inquiry')
            ->assertJsonMissing(['message' => 'CSRF token mismatch.']);

        $this->assertDatabaseHas('contact_inquiries', [
            'customer_id' => $customer->id,
            'email' => 'verified@example.com',
            'mobile' => '9462577065',
            'service_interested' => 'Wedding Planning',
        ]);
    }

    public function test_customer_without_registered_mobile_cannot_submit(): void
    {
        $customer = Customer::factory()->create(['phone' => null]);

        $this->postInquiry($this->payload($customer, [
            'mobile' => '9462577065',
            'email' => $customer->email,
        ]), $customer)->assertUnprocessable();

        $this->assertSame(0, ContactInquiry::query()->count());
    }

    public function test_invalid_inquiry_mobile_is_rejected(): void
    {
        $customer = Customer::factory()->create(['phone' => '9462577065']);
        $invalid = [
            '+919462577065',
            '94625 77065',
            '94625-77065',
            '946257706a',
            '946257706!',
            '94625770651',
            '919462577065',
        ];

        foreach ($invalid as $mobile) {
            $this->postInquiry($this->payload($customer, ['mobile' => $mobile]), $customer)
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['mobile']);
        }

        $this->assertSame(0, ContactInquiry::query()->count());
    }

    public function test_ten_digits_starting_with_any_digit_are_not_rejected(): void
    {
        foreach (['0462577065', '2462577065', '5462577065'] as $i => $phone) {
            \Illuminate\Support\Facades\RateLimiter::clear('contact:127.0.0.1');
            $customer = Customer::factory()->create([
                'email' => "digit{$i}@example.com",
                'phone' => $phone,
            ]);

            $this->postInquiry($this->payload($customer), $customer)->assertCreated();
        }
    }

    public function test_phone_mismatch_is_rejected(): void
    {
        $customer = Customer::factory()->create(['phone' => '9462577065']);

        $this->postInquiry($this->payload($customer, ['mobile' => '9876543210']), $customer)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['mobile']);

        $this->assertSame(0, ContactInquiry::query()->count());
    }

    public function test_email_mismatch_is_rejected(): void
    {
        $customer = Customer::factory()->create([
            'email' => 'mine@example.com',
            'phone' => '9462577065',
        ]);

        $this->postInquiry($this->payload($customer, ['email' => 'other@example.com']), $customer)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        $this->assertSame(0, ContactInquiry::query()->count());
    }

    public function test_unregistered_email_is_rejected(): void
    {
        $customer = Customer::factory()->create([
            'email' => 'mine@example.com',
            'phone' => '9462577065',
        ]);

        $this->postInquiry($this->payload($customer, ['email' => 'nobody@example.com']), $customer)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_frontend_customer_id_is_ignored_and_session_customer_is_stored(): void
    {
        $customer = Customer::factory()->create(['phone' => '9462577065']);
        $other = Customer::factory()->create([
            'email' => 'other@example.com',
            'phone' => '9876543210',
        ]);

        $this->postInquiry($this->payload($customer, [
            'customer_id' => $other->id,
            'name' => 'Impersonated',
        ]), $customer)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['customer_id']);

        $this->postInquiry($this->payload($customer, [
            'name' => 'Impersonated Name',
        ]), $customer)
            ->assertCreated()
            ->assertJsonPath('data.customer_id', $customer->id)
            ->assertJsonPath('data.name', $customer->name)
            ->assertJsonPath('data.email', $customer->email);

        $this->assertDatabaseMissing('contact_inquiries', ['customer_id' => $other->id]);
    }

    public function test_customer_cannot_impersonate_another_customer(): void
    {
        $customerA = Customer::factory()->create([
            'name' => 'Customer A',
            'email' => 'a@example.com',
            'phone' => '9462577065',
        ]);
        $customerB = Customer::factory()->create([
            'name' => 'Customer B',
            'email' => 'b@example.com',
            'phone' => '9876543210',
        ]);

        $this->postInquiry($this->payload($customerB), $customerA)
            ->assertUnprocessable();

        $this->assertSame(0, ContactInquiry::query()->count());
    }

    public function test_admin_inquiry_record_includes_customer_identity(): void
    {
        $customer = Customer::factory()->create([
            'name' => 'Admin Visible',
            'email' => 'admin-visible@example.com',
            'phone' => '1462577065',
        ]);

        $this->postInquiry($this->payload($customer), $customer)->assertCreated();

        $inquiry = ContactInquiry::query()->with('customer')->firstOrFail();
        $this->assertSame($customer->id, $inquiry->customer_id);
        $this->assertSame('Admin Visible', $inquiry->name);
        $this->assertSame('admin-visible@example.com', $inquiry->email);
        $this->assertSame('1462577065', $inquiry->mobile);
        $this->assertSame('Wedding Planning', $inquiry->service_interested);
        $this->assertSame('new', $inquiry->status->value);
        $this->assertNotNull($inquiry->created_at);
        $this->assertSame($customer->email, $inquiry->customer?->email);
    }
}

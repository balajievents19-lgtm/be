<?php

namespace Tests\Feature\Api;

use App\Models\ContactInquiry;
use App\Models\Customer;
use App\Models\CustomerSocialAccount;
use App\Models\EventType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\Support\CreatesWebsiteContent;
use Tests\TestCase;

class ContactApiTest extends TestCase
{
    use CreatesWebsiteContent;
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test User',
            'mobile' => '9876543210',
            'email' => 'test@example.com',
            'message' => 'Looking for wedding planning help.',
            'website' => '',
        ], $overrides);
    }

    private function verifiedCustomer(array $overrides = []): Customer
    {
        return Customer::factory()->create(array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '9876543210',
        ], $overrides));
    }

    private function asCustomer(Customer $customer)
    {
        return $this->actingAs($customer, 'customer');
    }

    public function test_guest_contact_is_unauthorized_and_creates_no_row(): void
    {
        $this->postJson('/api/contact', $this->validPayload())
            ->assertUnauthorized()
            ->assertJsonPath('code', 'customer_auth_required')
            ->assertJsonPath('message', 'Please verify your account before submitting an enquiry.');

        $this->assertSame(0, ContactInquiry::query()->count());
    }

    public function test_guest_slider_lead_is_unauthorized(): void
    {
        $eventType = EventType::query()->create([
            'name' => 'Wedding',
            'slug' => 'wedding',
            'status' => true,
            'sort_order' => 1,
        ]);

        $this->postJson('/api/contact', $this->validPayload([
            'subject' => 'Slider inquiry: Wedding',
            'event_type_id' => $eventType->id,
            'event_date' => '2030-12-20',
            'event_location' => 'Jaipur',
            'source' => 'slider',
        ]))->assertUnauthorized();

        $this->assertSame(0, ContactInquiry::query()->count());
    }

    public function test_unverified_customer_cannot_submit(): void
    {
        $customer = Customer::factory()->unverified()->create([
            'email' => 'test@example.com',
            'phone' => '9876543210',
        ]);

        $this->asCustomer($customer)
            ->postJson('/api/contact', $this->validPayload())
            ->assertForbidden()
            ->assertJsonPath('code', 'email_verification_required')
            ->assertJsonPath('message', 'Please verify your account before submitting an enquiry.');

        $this->assertSame(0, ContactInquiry::query()->count());
    }

    public function test_client_verified_flag_cannot_bypass_server_state(): void
    {
        $customer = Customer::factory()->unverified()->create([
            'email' => 'test@example.com',
            'phone' => '9876543210',
        ]);

        $this->asCustomer($customer)
            ->postJson('/api/contact', $this->validPayload([
                'verified' => true,
                'email_verified' => true,
            ]))
            ->assertForbidden();

        $this->assertSame(0, ContactInquiry::query()->count());
    }

    public function test_verified_customer_can_submit(): void
    {
        $customer = $this->verifiedCustomer();

        $this->asCustomer($customer)
            ->postJson('/api/contact', $this->validPayload())
            ->assertCreated()
            ->assertJsonPath('data.name', 'Test User')
            ->assertJsonPath('data.mobile', '9876543210')
            ->assertJsonPath('data.customer_id', $customer->id);

        $this->assertDatabaseHas('contact_inquiries', [
            'customer_id' => $customer->id,
            'email' => 'test@example.com',
            'mobile' => '9876543210',
        ]);
    }

    public function test_slider_lead_stores_event_fields_for_verified_customer(): void
    {
        $customer = $this->verifiedCustomer();
        $eventType = EventType::query()->create([
            'name' => 'Wedding',
            'slug' => 'wedding',
            'status' => true,
            'sort_order' => 1,
        ]);

        $this->asCustomer($customer)
            ->postJson('/api/contact', $this->validPayload([
                'subject' => 'Slider inquiry: Wedding',
                'event_type_id' => $eventType->id,
                'service_interested' => 'ignored-client-value',
                'event_date' => '2030-12-20',
                'event_location' => 'Jaipur',
                'budget' => '5-10 Lakh',
                'source' => 'slider',
                'message' => "Event Type: Wedding\nEvent Location: Jaipur\nEvent Date: 2030-12-20",
            ]))
            ->assertCreated()
            ->assertJsonPath('data.event_location', 'Jaipur')
            ->assertJsonPath('data.source', 'slider')
            ->assertJsonPath('data.service_interested', 'Wedding')
            ->assertJsonPath('data.customer_id', $customer->id);

        $this->assertDatabaseHas('contact_inquiries', [
            'source' => 'slider',
            'event_location' => 'Jaipur',
            'service_interested' => 'Wedding',
            'customer_id' => $customer->id,
        ]);
    }

    public function test_facebook_oauth_customer_can_submit(): void
    {
        $customer = Customer::factory()->unverified()->create([
            'email' => 'fb@example.com',
            'phone' => '9876543210',
        ]);
        CustomerSocialAccount::query()->create([
            'customer_id' => $customer->id,
            'provider' => 'facebook',
            'provider_user_id' => 'fb-server-id-1',
            'provider_email' => 'fb@example.com',
            'provider_name' => 'FB User',
        ]);

        $this->asCustomer($customer)
            ->postJson('/api/contact', $this->validPayload([
                'email' => 'fb@example.com',
            ]))
            ->assertCreated()
            ->assertJsonPath('data.customer_id', $customer->id);
    }

    public function test_mobile_otp_verified_customer_can_submit(): void
    {
        $customer = Customer::factory()->unverified()->create([
            'email' => 'sms@example.com',
            'phone' => '9876543210',
            'mobile_verified_at' => now(),
        ]);

        $this->asCustomer($customer)
            ->postJson('/api/contact', $this->validPayload([
                'email' => 'sms@example.com',
            ]))
            ->assertCreated();
    }

    public function test_slider_rejects_inactive_event_type_id(): void
    {
        $customer = $this->verifiedCustomer();
        $inactive = EventType::query()->create([
            'name' => 'Hidden Party',
            'slug' => 'hidden-party',
            'status' => false,
            'sort_order' => 1,
        ]);

        $this->asCustomer($customer)
            ->postJson('/api/contact', $this->validPayload([
                'event_type_id' => $inactive->id,
                'source' => 'slider',
                'event_date' => '2030-12-20',
                'event_location' => 'Jaipur',
                'message' => 'test',
            ]))->assertStatus(422)
            ->assertJsonValidationErrors(['event_type_id']);
    }

    public function test_contact_rejects_past_event_date(): void
    {
        $customer = $this->verifiedCustomer();

        $this->asCustomer($customer)
            ->postJson('/api/contact', $this->validPayload([
                'event_date' => '2020-01-01',
            ]))->assertStatus(422)
            ->assertJsonValidationErrors(['event_date']);
    }

    public function test_service_inquiry_guest_is_unauthorized(): void
    {
        $this->postJson('/api/contact', $this->validPayload([
            'email' => 'guest@example.com',
            'subject' => 'Service inquiry: Wedding Planning',
            'service_interested' => 'Wedding Planning',
            'event_date' => '2031-07-20',
            'source' => 'service_inquiry',
            'message' => 'Inquiry for Wedding Planning',
        ]))->assertUnauthorized();

        $this->assertSame(0, ContactInquiry::query()->count());
    }

    public function test_package_inquiry_guest_is_unauthorized(): void
    {
        $this->postJson('/api/contact', $this->validPayload([
            'email' => 'guest@example.com',
            'subject' => 'Package Enquiry: Premium Wedding Package',
            'service_interested' => 'Premium Wedding Package',
            'source' => 'package_inquiry',
            'message' => 'Interested in the Premium Wedding Package.',
        ]))->assertUnauthorized();

        $this->assertSame(0, ContactInquiry::query()->count());
    }

    public function test_contact_rejects_invalid_phone(): void
    {
        $customer = $this->verifiedCustomer();

        $this->asCustomer($customer)
            ->postJson('/api/contact', $this->validPayload([
                'mobile' => '12345',
            ]))->assertStatus(422)
            ->assertJsonValidationErrors(['mobile']);
    }

    public function test_slider_requires_event_date_and_location(): void
    {
        $customer = $this->verifiedCustomer();
        $eventType = EventType::query()->create([
            'name' => 'Wedding',
            'slug' => 'wedding-required-fields',
            'status' => true,
            'sort_order' => 1,
        ]);

        $this->asCustomer($customer)
            ->postJson('/api/contact', $this->validPayload([
                'event_type_id' => $eventType->id,
                'source' => 'slider',
            ]))->assertStatus(422)
            ->assertJsonValidationErrors(['event_date', 'event_location']);
    }

    public function test_contact_notifies_team_when_enquiry_email_is_configured(): void
    {
        \Illuminate\Support\Facades\Notification::fake();

        $this->seedSettings([
            'email' => 'leads@balaji.test',
        ]);

        $customer = $this->verifiedCustomer();

        $this->asCustomer($customer)
            ->postJson('/api/contact', $this->validPayload())
            ->assertCreated();

        \Illuminate\Support\Facades\Notification::assertSentOnDemand(\App\Notifications\NewContactInquiryNotification::class);
    }

    public function test_unverified_rejection_does_not_notify_team(): void
    {
        \Illuminate\Support\Facades\Notification::fake();
        $this->seedSettings(['email' => 'leads@balaji.test']);

        $this->postJson('/api/contact', $this->validPayload())->assertUnauthorized();

        \Illuminate\Support\Facades\Notification::assertNothingSent();
    }

    public function test_contact_rejects_missing_required_fields(): void
    {
        $this->postJson('/api/contact', [
            'name' => 'Only Name',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['mobile', 'message']);
    }

    public function test_contact_rejects_honeypot_spam(): void
    {
        $this->postJson('/api/contact', $this->validPayload([
            'website' => 'https://spam.example',
        ]))->assertStatus(422)
            ->assertJsonValidationErrors(['website']);

        $this->assertSame(0, ContactInquiry::query()->count());
    }

    public function test_contact_ignores_elevated_mass_assignment_fields(): void
    {
        $customer = $this->verifiedCustomer();

        $this->asCustomer($customer)
            ->postJson('/api/contact', $this->validPayload([
                'status' => 'closed',
                'priority' => 'urgent',
                'assigned_to' => 1,
                'admin_notes' => 'hacked',
            ]))->assertCreated();

        $inquiry = ContactInquiry::query()->firstOrFail();
        $this->assertSame('new', $inquiry->status->value);
        $this->assertSame('medium', $inquiry->priority->value);
        $this->assertNull($inquiry->assigned_to);
        $this->assertNull($inquiry->admin_notes);
    }

    public function test_contact_is_rate_limited(): void
    {
        RateLimiter::clear('contact:'.'127.0.0.1');
        $customer = $this->verifiedCustomer();

        for ($i = 0; $i < 5; $i++) {
            $this->asCustomer($customer)
                ->postJson('/api/contact', $this->validPayload([
                    'message' => 'Looking for wedding planning help. '.$i,
                ]))->assertCreated();
        }

        $this->asCustomer($customer)
            ->postJson('/api/contact', $this->validPayload([
                'message' => 'Looking for wedding planning help. extra',
            ]))->assertStatus(429);
    }

    public function test_contact_from_frontend_origin_does_not_csrf_mismatch(): void
    {
        config(['sanctum.stateful' => ['localhost', 'localhost:3000', '127.0.0.1', '127.0.0.1:3000']]);
        $customer = $this->verifiedCustomer();

        $this->asCustomer($customer)->withHeaders([
            'Origin' => 'http://localhost:3000',
            'Referer' => 'http://localhost:3000/',
        ])->postJson('/api/contact', $this->validPayload([
            'email' => 'test@example.com',
        ]))->assertCreated()->assertJsonMissing(['message' => 'CSRF token mismatch.']);
    }
}

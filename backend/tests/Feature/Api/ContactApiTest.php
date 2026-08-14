<?php

namespace Tests\Feature\Api;

use App\Models\ContactInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ContactApiTest extends TestCase
{
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

    public function test_contact_store_creates_inquiry(): void
    {
        $this->postJson('/api/contact', $this->validPayload())
            ->assertCreated()
            ->assertJsonPath('data.name', 'Test User')
            ->assertJsonPath('data.mobile', '9876543210');

        $this->assertDatabaseHas('contact_inquiries', [
            'name' => 'Test User',
            'mobile' => '9876543210',
            'email' => 'test@example.com',
        ]);
    }

    public function test_slider_lead_stores_event_fields_and_source(): void
    {
        $this->postJson('/api/contact', $this->validPayload([
            'subject' => 'Slider inquiry: Wedding Planning',
            'service_interested' => 'Wedding Planning',
            'event_date' => '2030-12-20',
            'event_location' => 'Jaipur',
            'budget' => '5-10 Lakh',
            'source' => 'slider',
            'message' => "Event Type: Wedding Planning\nEvent Location: Jaipur\nEvent Date: 2030-12-20",
        ]))
            ->assertCreated()
            ->assertJsonPath('data.event_location', 'Jaipur')
            ->assertJsonPath('data.source', 'slider');

        $this->assertDatabaseHas('contact_inquiries', [
            'source' => 'slider',
            'event_location' => 'Jaipur',
            'service_interested' => 'Wedding Planning',
        ]);
    }

    public function test_service_inquiry_lead_stores_service_and_source(): void
    {
        $this->postJson('/api/contact', $this->validPayload([
            'subject' => 'Service inquiry: Wedding Planning',
            'service_interested' => 'Wedding Planning',
            'event_date' => '2031-07-20',
            'source' => 'service_inquiry',
            'message' => 'Inquiry for Wedding Planning',
        ]))
            ->assertCreated()
            ->assertJsonPath('data.source', 'service_inquiry')
            ->assertJsonPath('data.service_interested', 'Wedding Planning');

        $this->assertDatabaseHas('contact_inquiries', [
            'source' => 'service_inquiry',
            'service_interested' => 'Wedding Planning',
        ]);

        $inquiry = ContactInquiry::query()->latest('id')->firstOrFail();
        $this->assertSame('new', $inquiry->status->value);
        $this->assertSame(1, ContactInquiry::query()->where('source', 'service_inquiry')->count());
    }

    public function test_package_inquiry_lead_stores_package_context_and_source(): void
    {
        $this->postJson('/api/contact', $this->validPayload([
            'subject' => 'Package Enquiry: Premium Wedding Package',
            'service_interested' => 'Premium Wedding Package',
            'source' => 'package_inquiry',
            'message' => 'Interested in the Premium Wedding Package.',
        ]))
            ->assertCreated()
            ->assertJsonPath('data.source', 'package_inquiry')
            ->assertJsonPath('data.service_interested', 'Premium Wedding Package')
            ->assertJsonPath('data.subject', 'Package Enquiry: Premium Wedding Package');

        $this->assertDatabaseHas('contact_inquiries', [
            'source' => 'package_inquiry',
            'service_interested' => 'Premium Wedding Package',
            'subject' => 'Package Enquiry: Premium Wedding Package',
        ]);

        $inquiry = ContactInquiry::query()->latest('id')->firstOrFail();
        $this->assertSame('new', $inquiry->status->value);
        $this->assertSame('medium', $inquiry->priority->value);
        $this->assertNull($inquiry->assigned_to);
        $this->assertSame(1, ContactInquiry::query()->where('source', 'package_inquiry')->count());
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
        $this->postJson('/api/contact', $this->validPayload([
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

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/contact', $this->validPayload([
                'mobile' => '900000000'.$i,
                'email' => "user{$i}@example.com",
            ]))->assertCreated();
        }

        $this->postJson('/api/contact', $this->validPayload([
            'mobile' => '9000000009',
            'email' => 'user9@example.com',
        ]))->assertStatus(429);
    }
}

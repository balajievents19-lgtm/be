<?php

namespace Tests\Unit;

use App\Models\ContactInquiry;
use App\Support\LeadContactLinks;
use PHPUnit\Framework\TestCase;

class LeadContactLinksTest extends TestCase
{
    public function test_call_url_uses_existing_mobile_digits_and_plus(): void
    {
        $this->assertSame('tel:+919462577065', LeadContactLinks::callUrl('+91-9462577065'));
        $this->assertNull(LeadContactLinks::callUrl(null));
        $this->assertNull(LeadContactLinks::callUrl('   '));
    }

    public function test_whatsapp_url_preserves_country_digits_and_encodes_message(): void
    {
        $lead = new ContactInquiry([
            'name' => 'Ravi Sharma',
            'mobile' => '+91-9462577065',
        ]);

        $url = LeadContactLinks::whatsappUrl($lead);

        $this->assertNotNull($url);
        $this->assertStringStartsWith('https://wa.me/919462577065?text=', $url);
        $this->assertStringContainsString(rawurlencode('Hello Ravi Sharma, this is Balaji Events. We received your event enquiry. How can we help you?'), $url);
    }

    public function test_whatsapp_url_without_name_uses_generic_greeting(): void
    {
        $lead = new ContactInquiry([
            'name' => '',
            'mobile' => '9462577065',
        ]);

        $url = LeadContactLinks::whatsappUrl($lead);

        $this->assertSame(
            'https://wa.me/9462577065?text='.rawurlencode('Hello, this is Balaji Events. We received your event enquiry. How can we help you?'),
            $url
        );
    }

    public function test_whatsapp_url_unavailable_without_mobile(): void
    {
        $lead = new ContactInquiry([
            'name' => 'Guest',
            'mobile' => '',
        ]);

        $this->assertNull(LeadContactLinks::whatsappUrl($lead));
    }
}

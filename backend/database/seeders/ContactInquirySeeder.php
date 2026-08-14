<?php

namespace Database\Seeders;

use App\Enums\ContactInquiryPriority;
use App\Enums\ContactInquiryStatus;
use App\Models\ContactInquiry;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContactInquirySeeder extends Seeder
{
    public function run(): void
    {
        ContactInquiry::query()->delete();

        $assigneeId = User::query()->value('id');

        $leads = [
            [
                'name' => 'Rahul Sharma',
                'mobile' => '+91-9876543210',
                'email' => 'rahul@example.com',
                'company' => null,
                'subject' => 'Wedding Planning Inquiry',
                'message' => 'Looking for full wedding planning support in Jhunjhunu for December.',
                'service_interested' => 'Event Planner',
                'event_date' => now()->addMonths(4)->toDateString(),
                'budget' => '5-8 Lakh',
                'status' => ContactInquiryStatus::New,
                'priority' => ContactInquiryPriority::High,
            ],
            [
                'name' => 'Priya Mehta',
                'mobile' => '+91-9123456780',
                'email' => 'priya@example.com',
                'company' => 'Mehta Exports',
                'subject' => 'Corporate Event Quote',
                'message' => 'Need a quotation for a 150-guest corporate dinner and stage décor.',
                'service_interested' => 'Stage Decorations',
                'event_date' => now()->addMonths(2)->toDateString(),
                'budget' => '2-3 Lakh',
                'status' => ContactInquiryStatus::Contacted,
                'priority' => ContactInquiryPriority::Medium,
            ],
            [
                'name' => 'Amit Verma',
                'mobile' => '+91-9988776655',
                'email' => null,
                'company' => null,
                'subject' => 'Photography Package',
                'message' => 'Interested in photography and videography for a reception.',
                'service_interested' => 'Photography',
                'event_date' => now()->addMonth()->toDateString(),
                'budget' => 'Under 1 Lakh',
                'status' => ContactInquiryStatus::FollowUp,
                'priority' => ContactInquiryPriority::Low,
            ],
        ];

        foreach ($leads as $index => $lead) {
            ContactInquiry::query()->create([
                ...$lead,
                'assigned_to' => $index === 0 ? $assigneeId : null,
                'admin_notes' => $index === 1 ? 'Called once. Waiting for guest count confirmation.' : null,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'ContactInquirySeeder',
            ]);
        }
    }
}

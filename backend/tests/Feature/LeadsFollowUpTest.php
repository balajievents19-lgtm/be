<?php

namespace Tests\Feature;

use App\Enums\ContactInquiryPriority;
use App\Enums\ContactInquiryStatus;
use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Filament\Resources\ContactInquiries\Pages\EditContactInquiry;
use App\Filament\Resources\ContactInquiries\Pages\ListContactInquiries;
use App\Models\ContactInquiry;
use App\Models\User;
use App\Support\LeadsCsvExporter;
use App\Support\Rbac\AdminModules;
use Carbon\Carbon;
use Database\Seeders\RBACSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LeadsFollowUpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RBACSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create([
            'email' => strtolower(str_replace(' ', '.', $role)).'.followup@balaji.test',
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function makeLead(array $overrides = []): ContactInquiry
    {
        return ContactInquiry::query()->create(array_merge([
            'name' => 'Follow-up Test Lead',
            'mobile' => '9000000600',
            'message' => 'Follow-up workflow test enquiry',
            'status' => ContactInquiryStatus::New,
            'priority' => ContactInquiryPriority::Medium,
            'source' => 'contact_page',
        ], $overrides));
    }

    public function test_follow_up_at_can_be_stored_and_is_cast_to_datetime(): void
    {
        $lead = $this->makeLead([
            'follow_up_at' => '2026-09-01 10:30:00',
        ]);

        $lead->refresh();

        $this->assertInstanceOf(Carbon::class, $lead->follow_up_at);
        $this->assertSame('2026-09-01 10:30', $lead->follow_up_at->format('Y-m-d H:i'));
    }

    public function test_lead_manager_can_update_follow_up_at_via_edit_form_without_changing_other_fields(): void
    {
        $actor = $this->userWithRole(AdminModules::ROLE_LEAD_MANAGER);
        $assignee = $this->userWithRole(AdminModules::ROLE_SUPER_ADMIN);
        $lead = $this->makeLead([
            'status' => ContactInquiryStatus::Contacted,
            'priority' => ContactInquiryPriority::High,
            'assigned_to' => $assignee->id,
            'source' => 'slider',
        ]);

        $this->actingAs($actor);

        Livewire::test(EditContactInquiry::class, ['record' => $lead->getKey()])
            ->assertSuccessful()
            ->fillForm([
                'follow_up_at' => '2026-09-15 14:00:00',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $lead->refresh();

        $this->assertSame('2026-09-15 14:00', $lead->follow_up_at?->format('Y-m-d H:i'));
        $this->assertSame(ContactInquiryStatus::Contacted, $lead->status);
        $this->assertSame(ContactInquiryPriority::High, $lead->priority);
        $this->assertSame($assignee->id, $lead->assigned_to);
        $this->assertSame('slider', $lead->source);
    }

    public function test_super_admin_can_clear_follow_up_at_via_edit_form(): void
    {
        $actor = $this->userWithRole(AdminModules::ROLE_SUPER_ADMIN);
        $lead = $this->makeLead([
            'follow_up_at' => '2026-09-20 09:00:00',
            'status' => ContactInquiryStatus::FollowUp,
        ]);

        $this->actingAs($actor);

        Livewire::test(EditContactInquiry::class, ['record' => $lead->getKey()])
            ->fillForm([
                'follow_up_at' => null,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertNull($lead->fresh()->follow_up_at);
        $this->assertSame(ContactInquiryStatus::FollowUp, $lead->fresh()->status);
    }

    public function test_unauthorized_roles_cannot_update_follow_up_at(): void
    {
        $lead = $this->makeLead();

        $content = $this->userWithRole(AdminModules::ROLE_CONTENT_MANAGER);
        $this->actingAs($content);
        $this->assertFalse($content->can('update', $lead));
        $this->get(ContactInquiryResource::getUrl('edit', ['record' => $lead]))
            ->assertForbidden();

        $newsletter = $this->userWithRole(AdminModules::ROLE_NEWSLETTER_MANAGER);
        $this->actingAs($newsletter);
        $this->assertFalse($newsletter->can('update', $lead));
        $this->get(ContactInquiryResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_quick_view_and_table_expose_follow_up_at(): void
    {
        $user = $this->userWithRole(AdminModules::ROLE_LEAD_MANAGER);
        $lead = $this->makeLead([
            'mobile' => '9462577065',
            'follow_up_at' => '2026-10-01 11:15:00',
        ]);

        $this->actingAs($user);

        Livewire::test(ListContactInquiries::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$lead])
            ->assertTableColumnExists('follow_up_at')
            ->assertTableFilterExists('follow_up_at')
            ->assertTableFilterExists('follow_up_range')
            ->assertSee('01 Oct 2026 11:15')
            ->assertTableActionExists('view')
            ->assertTableActionExists('edit')
            ->assertTableActionExists('call')
            ->assertTableActionExists('whatsapp')
            ->assertTableBulkActionExists('setFollowUp')
            ->assertTableBulkActionExists('clearFollowUp')
            ->assertTableBulkActionExists('updateStatus')
            ->assertTableBulkActionExists('updatePriority')
            ->assertTableBulkActionExists('assignLeads')
            ->assertTableBulkActionExists('delete')
            ->assertTableActionExists('exportLeads');
    }

    public function test_follow_up_scheduled_and_unscheduled_filters_work(): void
    {
        $user = $this->userWithRole(AdminModules::ROLE_LEAD_MANAGER);
        $scheduled = $this->makeLead([
            'name' => 'Scheduled Lead',
            'mobile' => '9000000601',
            'follow_up_at' => '2026-11-01 10:00:00',
        ]);
        $unscheduled = $this->makeLead([
            'name' => 'Unscheduled Lead',
            'mobile' => '9000000602',
            'follow_up_at' => null,
        ]);

        $this->actingAs($user);

        Livewire::test(ListContactInquiries::class)
            ->filterTable('follow_up_at', true)
            ->assertCanSeeTableRecords([$scheduled])
            ->assertCanNotSeeTableRecords([$unscheduled])
            ->filterTable('follow_up_at', false)
            ->assertCanSeeTableRecords([$unscheduled])
            ->assertCanNotSeeTableRecords([$scheduled]);
    }

    public function test_follow_up_column_is_sortable(): void
    {
        $user = $this->userWithRole(AdminModules::ROLE_LEAD_MANAGER);
        $later = $this->makeLead([
            'name' => 'Later Follow-up',
            'mobile' => '9000000603',
            'follow_up_at' => '2026-12-20 10:00:00',
        ]);
        $sooner = $this->makeLead([
            'name' => 'Sooner Follow-up',
            'mobile' => '9000000604',
            'follow_up_at' => '2026-12-01 10:00:00',
        ]);

        $this->actingAs($user);

        Livewire::test(ListContactInquiries::class)
            ->sortTable('follow_up_at')
            ->assertCanSeeTableRecords([$sooner, $later], inOrder: true)
            ->sortTable('follow_up_at', 'desc')
            ->assertCanSeeTableRecords([$later, $sooner], inOrder: true);
    }

    public function test_bulk_set_and_clear_follow_up_affect_selected_leads_only(): void
    {
        $actor = $this->userWithRole(AdminModules::ROLE_LEAD_MANAGER);
        $assignee = $this->userWithRole(AdminModules::ROLE_SUPER_ADMIN);

        $selectedA = $this->makeLead([
            'name' => 'Bulk FU A',
            'mobile' => '9000000611',
            'status' => ContactInquiryStatus::New,
            'priority' => ContactInquiryPriority::Low,
            'assigned_to' => $assignee->id,
            'source' => 'contact_page',
        ]);
        $selectedB = $this->makeLead([
            'name' => 'Bulk FU B',
            'mobile' => '9000000612',
            'status' => ContactInquiryStatus::Contacted,
            'priority' => ContactInquiryPriority::High,
            'assigned_to' => $assignee->id,
            'source' => 'service_inquiry',
        ]);
        $unselected = $this->makeLead([
            'name' => 'Bulk FU Unselected',
            'mobile' => '9000000613',
            'follow_up_at' => '2026-08-01 08:00:00',
            'status' => ContactInquiryStatus::Won,
            'priority' => ContactInquiryPriority::Medium,
            'assigned_to' => $actor->id,
        ]);

        $this->actingAs($actor);

        Livewire::test(ListContactInquiries::class)
            ->callTableBulkAction('setFollowUp', [$selectedA, $selectedB], [
                'follow_up_at' => '2026-09-30 16:45:00',
            ])
            ->assertHasNoTableBulkActionErrors();

        $selectedA->refresh();
        $selectedB->refresh();
        $unselected->refresh();

        $this->assertSame('2026-09-30 16:45', $selectedA->follow_up_at?->format('Y-m-d H:i'));
        $this->assertSame('2026-09-30 16:45', $selectedB->follow_up_at?->format('Y-m-d H:i'));
        $this->assertSame('2026-08-01 08:00', $unselected->follow_up_at?->format('Y-m-d H:i'));

        $this->assertSame(ContactInquiryStatus::New, $selectedA->status);
        $this->assertSame(ContactInquiryStatus::Contacted, $selectedB->status);
        $this->assertSame(ContactInquiryPriority::Low, $selectedA->priority);
        $this->assertSame(ContactInquiryPriority::High, $selectedB->priority);
        $this->assertSame($assignee->id, $selectedA->assigned_to);
        $this->assertSame($assignee->id, $selectedB->assigned_to);
        $this->assertSame($actor->id, $unselected->assigned_to);
        $this->assertSame(ContactInquiryStatus::Won, $unselected->status);

        Livewire::test(ListContactInquiries::class)
            ->callTableBulkAction('clearFollowUp', [$selectedA, $selectedB])
            ->assertHasNoTableBulkActionErrors();

        $this->assertNull($selectedA->fresh()->follow_up_at);
        $this->assertNull($selectedB->fresh()->follow_up_at);
        $this->assertSame('2026-08-01 08:00', $unselected->fresh()->follow_up_at?->format('Y-m-d H:i'));
        $this->assertSame(ContactInquiryStatus::New, $selectedA->fresh()->status);
        $this->assertSame($assignee->id, $selectedA->fresh()->assigned_to);
    }

    public function test_csv_export_includes_follow_up_at(): void
    {
        $lead = $this->makeLead([
            'follow_up_at' => '2026-09-05 13:20:00',
            'status' => ContactInquiryStatus::FollowUp,
            'priority' => ContactInquiryPriority::High,
        ]);
        $lead->load('assignee');

        $csv = LeadsCsvExporter::toCsv([$lead]);

        $this->assertStringContainsString('Follow-up At', $csv);
        $this->assertStringContainsString('2026-09-05 13:20', $csv);
        $this->assertStringContainsString('Follow Up', $csv);
        $this->assertStringContainsString('High', $csv);
    }
}

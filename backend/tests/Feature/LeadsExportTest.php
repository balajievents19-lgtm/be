<?php

namespace Tests\Feature;

use App\Enums\ContactInquiryPriority;
use App\Enums\ContactInquiryStatus;
use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Filament\Resources\ContactInquiries\Pages\ListContactInquiries;
use App\Models\ContactInquiry;
use App\Models\User;
use App\Support\LeadsCsvExporter;
use App\Support\Rbac\AdminModules;
use Database\Seeders\RBACSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LeadsExportTest extends TestCase
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
            'email' => strtolower(str_replace(' ', '.', $role)).'.export@balaji.test',
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function makeLead(array $overrides = []): ContactInquiry
    {
        return ContactInquiry::query()->create(array_merge([
            'name' => 'Export Test Lead',
            'mobile' => '9000000500',
            'email' => 'export.lead@example.test',
            'subject' => 'Wedding enquiry',
            'message' => 'Need a wedding planner.',
            'service_interested' => 'Wedding Planning',
            'event_date' => '2026-12-01',
            'event_location' => 'Jaipur',
            'budget' => '500000',
            'source' => 'contact_page',
            'status' => ContactInquiryStatus::New,
            'priority' => ContactInquiryPriority::Medium,
            'admin_notes' => 'Call after 5pm',
        ], $overrides));
    }

    public function test_csv_exporter_maps_existing_lead_fields_including_assignee_name(): void
    {
        $assignee = $this->userWithRole(AdminModules::ROLE_LEAD_MANAGER);
        $assignee->forceFill(['name' => 'Lead Worker'])->save();

        $lead = $this->makeLead([
            'status' => ContactInquiryStatus::FollowUp,
            'priority' => ContactInquiryPriority::High,
            'source' => 'service_inquiry',
            'assigned_to' => $assignee->id,
        ]);
        $lead->load('assignee');

        $csv = LeadsCsvExporter::toCsv([$lead]);
        $headerLine = strtok($csv, "\r\n") ?: '';

        $this->assertSame(LeadsCsvExporter::toCsv([]), $headerLine."\n");
        $this->assertStringContainsString('Lead Worker', $csv);
        $this->assertStringContainsString('Follow Up', $csv);
        $this->assertStringContainsString('High', $csv);
        $this->assertStringContainsString('service_inquiry', $csv);
        $this->assertStringContainsString('2026-12-01', $csv);
        $this->assertStringContainsString('Wedding Planning', $csv);
        $this->assertStringNotContainsString('leads.view', $csv);
        $this->assertStringNotContainsString('password', strtolower($csv));
    }

    public function test_lead_manager_can_export_filtered_leads(): void
    {
        $user = $this->userWithRole(AdminModules::ROLE_LEAD_MANAGER);
        $match = $this->makeLead([
            'name' => 'Follow Up Match',
            'mobile' => '9000000501',
            'status' => ContactInquiryStatus::FollowUp,
        ]);
        $other = $this->makeLead([
            'name' => 'New Other',
            'mobile' => '9000000502',
            'status' => ContactInquiryStatus::New,
        ]);

        $this->actingAs($user);

        Livewire::test(ListContactInquiries::class)
            ->assertSuccessful()
            ->assertTableActionExists('exportLeads')
            ->assertTableBulkActionExists('exportSelected')
            ->filterTable('status', ContactInquiryStatus::FollowUp->value)
            ->callTableAction('exportLeads')
            ->assertFileDownloaded();

        $csv = LeadsCsvExporter::toCsv(
            ContactInquiry::query()
                ->where('status', ContactInquiryStatus::FollowUp)
                ->with('assignee')
                ->get()
        );

        $this->assertStringContainsString($match->name, $csv);
        $this->assertStringNotContainsString($other->name, $csv);
    }

    public function test_super_admin_can_export_selected_leads_only(): void
    {
        $user = $this->userWithRole(AdminModules::ROLE_SUPER_ADMIN);
        $selected = $this->makeLead(['name' => 'Selected Export', 'mobile' => '9000000503']);
        $unselected = $this->makeLead(['name' => 'Unselected Export', 'mobile' => '9000000504']);

        $this->actingAs($user);

        Livewire::test(ListContactInquiries::class)
            ->assertSuccessful()
            ->callTableBulkAction('exportSelected', [$selected])
            ->assertFileDownloaded();

        $csv = LeadsCsvExporter::toCsv([$selected->fresh()->load('assignee')]);
        $this->assertStringContainsString($selected->name, $csv);
        $this->assertStringNotContainsString($unselected->name, $csv);
    }

    public function test_content_and_newsletter_managers_cannot_export_leads(): void
    {
        $content = $this->userWithRole(AdminModules::ROLE_CONTENT_MANAGER);
        $this->actingAs($content);
        $this->assertFalse($content->can('leads.view'));
        $this->get(ContactInquiryResource::getUrl('index'))->assertForbidden();

        $newsletter = $this->userWithRole(AdminModules::ROLE_NEWSLETTER_MANAGER);
        $this->actingAs($newsletter);
        $this->assertFalse($newsletter->can('leads.view'));
        $this->get(ContactInquiryResource::getUrl('index'))->assertForbidden();
    }

    public function test_existing_leads_actions_remain_available_with_export(): void
    {
        $user = $this->userWithRole(AdminModules::ROLE_LEAD_MANAGER);
        $lead = $this->makeLead(['mobile' => '9462577065']);

        $this->actingAs($user);

        Livewire::test(ListContactInquiries::class)
            ->assertSuccessful()
            ->assertTableActionExists('view')
            ->assertTableActionExists('edit')
            ->assertTableActionExists('call')
            ->assertTableActionExists('whatsapp')
            ->assertTableActionExists('exportLeads')
            ->assertTableBulkActionExists('updateStatus')
            ->assertTableBulkActionExists('updatePriority')
            ->assertTableBulkActionExists('assignLeads')
            ->assertTableBulkActionExists('delete')
            ->assertCanSeeTableRecords([$lead]);
    }
}

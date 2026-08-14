<?php

namespace Tests\Feature;

use App\Enums\ContactInquiryPriority;
use App\Enums\ContactInquiryStatus;
use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Filament\Resources\ContactInquiries\Pages\ListContactInquiries;
use App\Models\ContactInquiry;
use App\Models\User;
use App\Support\LeadAssignees;
use App\Support\Rbac\AdminModules;
use Database\Seeders\RBACSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LeadsBulkActionsTest extends TestCase
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
            'email' => strtolower(str_replace(' ', '.', $role)).'.bulk@balaji.test',
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function makeLead(array $overrides = []): ContactInquiry
    {
        return ContactInquiry::query()->create(array_merge([
            'name' => 'Bulk Test Lead',
            'mobile' => '9000000100',
            'message' => 'Bulk management test enquiry',
            'status' => ContactInquiryStatus::New,
            'priority' => ContactInquiryPriority::Medium,
        ], $overrides));
    }

    public function test_lead_manager_can_bulk_update_status_only_on_selected_leads(): void
    {
        $user = $this->userWithRole(AdminModules::ROLE_LEAD_MANAGER);
        $selectedA = $this->makeLead(['name' => 'Selected A', 'mobile' => '9000000101']);
        $selectedB = $this->makeLead(['name' => 'Selected B', 'mobile' => '9000000102']);
        $unselected = $this->makeLead([
            'name' => 'Unselected',
            'mobile' => '9000000103',
            'status' => ContactInquiryStatus::Contacted,
            'priority' => ContactInquiryPriority::High,
        ]);

        $this->actingAs($user);

        Livewire::test(ListContactInquiries::class)
            ->assertSuccessful()
            ->assertTableBulkActionExists('updateStatus')
            ->assertTableBulkActionExists('updatePriority')
            ->assertTableBulkActionExists('delete')
            ->callTableBulkAction('updateStatus', [$selectedA, $selectedB], [
                'status' => ContactInquiryStatus::FollowUp->value,
            ])
            ->assertHasNoTableBulkActionErrors();

        $this->assertSame(ContactInquiryStatus::FollowUp, $selectedA->fresh()->status);
        $this->assertSame(ContactInquiryStatus::FollowUp, $selectedB->fresh()->status);
        $this->assertSame(ContactInquiryStatus::Contacted, $unselected->fresh()->status);
        $this->assertSame(ContactInquiryPriority::Medium, $selectedA->fresh()->priority);
        $this->assertSame(ContactInquiryPriority::High, $unselected->fresh()->priority);
    }

    public function test_super_admin_can_bulk_update_priority_only_on_selected_leads(): void
    {
        $user = $this->userWithRole(AdminModules::ROLE_SUPER_ADMIN);
        $selectedA = $this->makeLead(['name' => 'Priority A', 'mobile' => '9000000201']);
        $selectedB = $this->makeLead(['name' => 'Priority B', 'mobile' => '9000000202']);
        $unselected = $this->makeLead([
            'name' => 'Priority Unselected',
            'mobile' => '9000000203',
            'status' => ContactInquiryStatus::Won,
            'priority' => ContactInquiryPriority::Low,
        ]);

        $this->actingAs($user);

        Livewire::test(ListContactInquiries::class)
            ->assertSuccessful()
            ->assertTableBulkActionExists('updatePriority')
            ->callTableBulkAction('updatePriority', [$selectedA, $selectedB], [
                'priority' => ContactInquiryPriority::High->value,
            ])
            ->assertHasNoTableBulkActionErrors();

        $this->assertSame(ContactInquiryPriority::High, $selectedA->fresh()->priority);
        $this->assertSame(ContactInquiryPriority::High, $selectedB->fresh()->priority);
        $this->assertSame(ContactInquiryPriority::Low, $unselected->fresh()->priority);
        $this->assertSame(ContactInquiryStatus::New, $selectedA->fresh()->status);
        $this->assertSame(ContactInquiryStatus::Won, $unselected->fresh()->status);
    }

    public function test_content_and_newsletter_managers_cannot_access_leads_bulk_actions(): void
    {
        $content = $this->userWithRole(AdminModules::ROLE_CONTENT_MANAGER);
        $this->actingAs($content);
        $this->assertFalse($content->can('leads.update'));
        $this->get(ContactInquiryResource::getUrl('index'))
            ->assertForbidden();

        $newsletter = $this->userWithRole(AdminModules::ROLE_NEWSLETTER_MANAGER);
        $this->actingAs($newsletter);
        $this->assertFalse($newsletter->can('leads.update'));
        $this->get(ContactInquiryResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_existing_row_actions_remain_on_leads_table(): void
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
            ->assertTableBulkActionExists('assignLeads')
            ->assertCanSeeTableRecords([$lead]);
    }

    public function test_lead_manager_can_bulk_assign_and_unassign_selected_leads(): void
    {
        $actor = $this->userWithRole(AdminModules::ROLE_LEAD_MANAGER);
        $assignee = $this->userWithRole(AdminModules::ROLE_SUPER_ADMIN);
        $assignee->forceFill(['name' => 'Assignable Admin'])->save();

        $selectedA = $this->makeLead([
            'name' => 'Assign A',
            'mobile' => '9000000301',
            'source' => 'contact_page',
        ]);
        $selectedB = $this->makeLead([
            'name' => 'Assign B',
            'mobile' => '9000000302',
            'source' => 'slider',
            'status' => ContactInquiryStatus::Contacted,
            'priority' => ContactInquiryPriority::High,
        ]);
        $unselected = $this->makeLead([
            'name' => 'Assign Unselected',
            'mobile' => '9000000303',
            'source' => 'service_inquiry',
            'assigned_to' => $actor->id,
        ]);

        $this->actingAs($actor);

        Livewire::test(ListContactInquiries::class)
            ->assertSuccessful()
            ->assertTableBulkActionExists('assignLeads')
            ->callTableBulkAction('assignLeads', [$selectedA, $selectedB], [
                'assigned_to' => $assignee->id,
            ])
            ->assertHasNoTableBulkActionErrors();

        $this->assertSame($assignee->id, $selectedA->fresh()->assigned_to);
        $this->assertSame($assignee->id, $selectedB->fresh()->assigned_to);
        $this->assertSame($actor->id, $unselected->fresh()->assigned_to);
        $this->assertSame('Assignable Admin', $selectedA->fresh()->assignee?->name);
        $this->assertSame(ContactInquiryStatus::New, $selectedA->fresh()->status);
        $this->assertSame(ContactInquiryStatus::Contacted, $selectedB->fresh()->status);
        $this->assertSame(ContactInquiryPriority::High, $selectedB->fresh()->priority);
        $this->assertSame('contact_page', $selectedA->fresh()->source);
        $this->assertSame('slider', $selectedB->fresh()->source);

        Livewire::test(ListContactInquiries::class)
            ->callTableBulkAction('assignLeads', [$selectedA, $selectedB], [
                'assigned_to' => null,
            ])
            ->assertHasNoTableBulkActionErrors();

        $this->assertNull($selectedA->fresh()->assigned_to);
        $this->assertNull($selectedB->fresh()->assigned_to);
        $this->assertSame($actor->id, $unselected->fresh()->assigned_to);
    }

    public function test_super_admin_can_bulk_assign_leads(): void
    {
        $actor = $this->userWithRole(AdminModules::ROLE_SUPER_ADMIN);
        $assignee = $this->userWithRole(AdminModules::ROLE_LEAD_MANAGER);
        $assignee->forceFill(['name' => 'Lead Worker'])->save();
        $lead = $this->makeLead(['name' => 'SA Assign', 'mobile' => '9000000401']);

        $this->actingAs($actor);

        Livewire::test(ListContactInquiries::class)
            ->callTableBulkAction('assignLeads', [$lead], [
                'assigned_to' => $assignee->id,
            ])
            ->assertHasNoTableBulkActionErrors();

        $this->assertSame($assignee->id, $lead->fresh()->assigned_to);
        $this->assertSame('Lead Worker', $lead->fresh()->assignee?->name);
    }

    public function test_content_manager_cannot_be_selected_as_assignee(): void
    {
        $content = $this->userWithRole(AdminModules::ROLE_CONTENT_MANAGER);
        $leadManager = $this->userWithRole(AdminModules::ROLE_LEAD_MANAGER);

        $this->assertFalse(LeadAssignees::isAssignable($content->id));
        $this->assertTrue(LeadAssignees::isAssignable($leadManager->id));
        $this->assertTrue(LeadAssignees::isAssignable(null));
    }
}

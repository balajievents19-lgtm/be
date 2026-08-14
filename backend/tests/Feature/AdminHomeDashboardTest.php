<?php

namespace Tests\Feature;

use App\Enums\ContactInquiryPriority;
use App\Enums\ContactInquiryStatus;
use App\Filament\Pages\Dashboard;
use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Filament\Widgets\LeadAttentionLists;
use App\Filament\Widgets\LeadBusinessBreakdowns;
use App\Filament\Widgets\LeadKpiOverview;
use App\Filament\Widgets\LeadPriorityChart;
use App\Filament\Widgets\LeadStatusChart;
use App\Models\ContactInquiry;
use App\Models\User;
use App\Support\Admin\LeadDashboardMetrics;
use App\Support\Rbac\AdminModules;
use Database\Seeders\RBACSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminHomeDashboardTest extends TestCase
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
            'email' => strtolower(str_replace(' ', '.', $role)).'.dash@balaji.test',
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function makeLead(array $overrides = []): ContactInquiry
    {
        $createdAt = $overrides['created_at'] ?? null;
        unset($overrides['created_at']);

        $lead = ContactInquiry::query()->create(array_merge([
            'name' => 'Dashboard Lead',
            'mobile' => '9000000800',
            'message' => 'Dashboard metrics test enquiry',
            'status' => ContactInquiryStatus::New,
            'priority' => ContactInquiryPriority::Medium,
            'source' => 'contact_page',
        ], $overrides));

        if ($createdAt !== null) {
            $lead->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();
        }

        return $lead->refresh();
    }

    public function test_authorized_admins_can_open_home_dashboard(): void
    {
        $super = $this->userWithRole(AdminModules::ROLE_SUPER_ADMIN);
        $this->actingAs($super)
            ->get(Dashboard::getUrl())
            ->assertOk();

        $leadManager = $this->userWithRole(AdminModules::ROLE_LEAD_MANAGER);
        $this->actingAs($leadManager)
            ->get(Dashboard::getUrl())
            ->assertOk();
    }

    public function test_lead_metrics_are_correct_and_period_aware(): void
    {
        $assignee = $this->userWithRole(AdminModules::ROLE_LEAD_MANAGER);

        $this->makeLead([
            'name' => 'New A',
            'mobile' => '9000000801',
            'status' => ContactInquiryStatus::New,
            'priority' => ContactInquiryPriority::High,
            'source' => 'service_inquiry',
            'service_interested' => 'Wedding Planning',
            'created_at' => now()->subDays(2),
        ]);
        $this->makeLead([
            'name' => 'Won B',
            'mobile' => '9000000802',
            'status' => ContactInquiryStatus::Won,
            'priority' => ContactInquiryPriority::Low,
            'source' => 'package_inquiry',
            'service_interested' => 'Royal Wedding Package',
            'assigned_to' => $assignee->id,
            'created_at' => now()->subDays(3),
        ]);
        $this->makeLead([
            'name' => 'Lost C',
            'mobile' => '9000000803',
            'status' => ContactInquiryStatus::Lost,
            'priority' => ContactInquiryPriority::Medium,
            'source' => 'slider',
            'created_at' => now()->subDays(40),
        ]);
        $this->makeLead([
            'name' => 'Overdue D',
            'mobile' => '9000000804',
            'status' => ContactInquiryStatus::Contacted,
            'follow_up_at' => now()->subDay(),
            'created_at' => now()->subDays(1),
        ]);
        $this->makeLead([
            'name' => 'Upcoming E',
            'mobile' => '9000000805',
            'status' => ContactInquiryStatus::FollowUp,
            'follow_up_at' => now()->addDay(),
            'created_at' => now()->subDays(1),
        ]);

        $metrics30 = new LeadDashboardMetrics(LeadDashboardMetrics::PERIOD_30D);
        $kpis = $metrics30->kpis();

        $this->assertSame(4, $kpis['total']);
        $this->assertSame(1, $kpis['new']);
        $this->assertSame(1, $kpis['won']);
        $this->assertSame(0, $kpis['lost']);
        $this->assertSame(2, $kpis['follow_ups_scheduled']);
        $this->assertSame(1, $kpis['overdue_follow_ups']);

        $this->assertSame(1, $metrics30->statusCounts()['new']);
        $this->assertSame(1, $metrics30->statusCounts()['won']);
        $this->assertSame(1, $metrics30->priorityCounts()['high']);
        $this->assertSame(1, $metrics30->sourceCounts()['service_inquiry']);
        $this->assertSame(1, $metrics30->sourceCounts()['package_inquiry']);

        $interests = $metrics30->interestBreakdown();
        $this->assertTrue($interests->contains(fn ($row): bool => $row->name === 'Wedding Planning' && $row->aggregate === 1));
        $this->assertTrue($interests->contains(fn ($row): bool => $row->name === 'Royal Wedding Package' && $row->source === 'package_inquiry'));

        $workload = $metrics30->assignmentWorkload();
        $this->assertTrue($workload->contains(fn ($row): bool => $row->label === 'Unassigned' && $row->aggregate >= 1));
        $this->assertTrue($workload->contains(fn ($row): bool => $row->user_id === $assignee->id && $row->aggregate === 1));

        $this->assertCount(1, $metrics30->overdueFollowUps());
        $this->assertCount(1, $metrics30->upcomingFollowUps());
        $this->assertTrue($metrics30->recentLeads()->contains(fn ($lead): bool => $lead->name === 'Overdue D'));

        $allTime = new LeadDashboardMetrics(LeadDashboardMetrics::PERIOD_ALL);
        $this->assertSame(5, $allTime->kpis()['total']);
        $this->assertSame(1, $allTime->kpis()['lost']);
    }

    public function test_super_admin_and_lead_manager_see_lead_widgets(): void
    {
        $this->makeLead();

        foreach ([AdminModules::ROLE_SUPER_ADMIN, AdminModules::ROLE_LEAD_MANAGER] as $role) {
            $user = $this->userWithRole($role);
            $this->actingAs($user);

            $this->assertTrue(ContactInquiryResource::canViewAny());
            $this->assertTrue(LeadKpiOverview::canView());
            $this->assertTrue(LeadStatusChart::canView());
            $this->assertTrue(LeadPriorityChart::canView());
            $this->assertTrue(LeadBusinessBreakdowns::canView());
            $this->assertTrue(LeadAttentionLists::canView());

            Livewire::test(Dashboard::class)
                ->assertSuccessful()
                ->assertSeeLivewire(LeadKpiOverview::class);
        }
    }

    public function test_content_and_newsletter_managers_do_not_see_lead_widgets(): void
    {
        $this->makeLead(['name' => 'Hidden Lead', 'mobile' => '9000000899']);

        foreach ([AdminModules::ROLE_CONTENT_MANAGER, AdminModules::ROLE_NEWSLETTER_MANAGER] as $role) {
            $user = $this->userWithRole($role);
            $this->actingAs($user);

            $this->assertFalse($user->can('leads.view'));
            $this->assertFalse(LeadKpiOverview::canView());
            $this->assertFalse(LeadStatusChart::canView());
            $this->assertFalse(LeadBusinessBreakdowns::canView());
            $this->assertFalse(LeadAttentionLists::canView());

            Livewire::test(Dashboard::class)
                ->assertSuccessful()
                ->assertDontSeeLivewire(LeadKpiOverview::class)
                ->assertDontSee('Hidden Lead')
                ->assertDontSee('9000000899');
        }
    }

    public function test_dashboard_period_filter_is_available_on_home(): void
    {
        $user = $this->userWithRole(AdminModules::ROLE_LEAD_MANAGER);
        $this->actingAs($user);

        Livewire::test(Dashboard::class)
            ->assertSuccessful()
            ->assertSee('Period')
            ->fillForm([
                'period' => LeadDashboardMetrics::PERIOD_TODAY,
            ], 'filtersForm')
            ->assertSet('filters.period', LeadDashboardMetrics::PERIOD_TODAY);
    }
}

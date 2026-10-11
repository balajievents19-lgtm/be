<?php

namespace Tests\Feature\Estimates;

use App\Console\Commands\ExpireEstimatesCommand;
use App\Enums\EstimateExpenseType;
use App\Enums\EstimateRateBasis;
use App\Enums\EstimateStatus;
use App\Models\Estimate;
use App\Models\EstimateCatalogueItem;
use App\Models\User;
use App\Support\Estimates\CustomerEstimateDocument;
use App\Support\Estimates\EstimateCatalogueWriter;
use App\Support\Estimates\EstimateNumberGenerator;
use App\Support\Estimates\EstimatePersister;
use App\Support\Rbac\AdminModules;
use Database\Seeders\RBACSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Tests\Support\CreatesWebsiteContent;
use Tests\TestCase;

class EstimateModuleTest extends TestCase
{
    use CreatesWebsiteContent;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RBACSeeder::class);
        $this->seedSettings([
            'company_tagline' => 'Trusted wedding & event management in Rajasthan',
            'phone' => '+91-9462577065',
            'whatsapp' => '+91-9462577065',
            'email' => 'balajievents19@gmail.com',
            'address' => 'Shop No.15, Road No.3, Opposite: Jamuna Resort, Jhunjhunu (Rajasthan)',
        ]);
    }

    public function test_estimate_numbers_follow_bre_year_sequence_and_are_unique(): void
    {
        $year = (int) now()->format('Y');
        $generator = app(EstimateNumberGenerator::class);

        $first = $generator->next($year);
        $second = $generator->next($year);

        $this->assertSame(sprintf('BRE-%d-0001', $year), $first);
        $this->assertSame(sprintf('BRE-%d-0002', $year), $second);
        $this->assertNotSame($first, $second);
    }

    public function test_new_year_restarts_sequence(): void
    {
        $generator = app(EstimateNumberGenerator::class);
        $this->assertSame('BRE-2026-0001', $generator->next(2026));
        $this->assertSame('BRE-2027-0001', $generator->next(2027));
    }

    public function test_valid_until_is_five_calendar_days_after_estimate_date(): void
    {
        $admin = $this->superAdmin();
        $estimate = $this->makeEstimate($admin, [
            'estimate_date' => '2026-10-11',
        ]);

        $this->assertSame('2026-10-16', $estimate->valid_until?->toDateString());
    }

    public function test_catalogue_item_can_be_found_and_reused(): void
    {
        $item = app(EstimateCatalogueWriter::class)->save([
            'name' => 'Stage décor',
            'default_rate' => 15000,
            'rate_basis' => EstimateRateBasis::PerDay->value,
            'actual_cost_rate' => 8000,
        ]);

        $found = EstimateCatalogueItem::query()
            ->active()
            ->where('name_normalized', EstimateCatalogueItem::normalizeName('Stage décor'))
            ->first();

        $this->assertNotNull($found);
        $this->assertTrue($found->is($item));

        $later = $this->makeEstimate($this->superAdmin(), [], [[
            'catalogue_item_id' => $item->id,
            'name' => $item->name,
            'qty' => 1,
            'rate' => $item->default_rate,
            'days' => 2,
            'rate_basis' => $item->rate_basis->value,
            'actual_cost_rate' => $item->actual_cost_rate,
        ]]);

        $this->assertSame($item->id, $later->lines->first()?->catalogue_item_id);
        $this->assertSame('30000.00', (string) $later->lines->first()?->amount);
    }

    public function test_duplicate_catalogue_names_are_rejected(): void
    {
        $writer = app(EstimateCatalogueWriter::class);
        $writer->save([
            'name' => 'Mandap',
            'default_rate' => 10000,
            'rate_basis' => EstimateRateBasis::OneTime->value,
        ]);

        $this->expectException(ValidationException::class);
        $writer->save([
            'name' => '  mandap ',
            'default_rate' => 12000,
            'rate_basis' => EstimateRateBasis::OneTime->value,
        ]);
    }

    public function test_expired_estimates_remain_stored_with_unchanged_totals(): void
    {
        $estimate = $this->makeEstimate($this->superAdmin(), [
            'estimate_date' => now()->subDays(10)->toDateString(),
            'status' => EstimateStatus::Sent->value,
        ]);
        $total = (string) $estimate->grand_total;
        $id = $estimate->id;

        Artisan::call(ExpireEstimatesCommand::class);

        $fresh = Estimate::query()->findOrFail($id);
        $this->assertSame(EstimateStatus::Expired, $fresh->status);
        $this->assertSame($total, (string) $fresh->grand_total);
        $this->assertSame(1, $fresh->lines()->count());
    }

    public function test_reloading_does_not_double_multiply_per_day_amounts(): void
    {
        $estimate = $this->makeEstimate($this->superAdmin());
        $line = $estimate->lines->first();
        $this->assertSame('30000.00', (string) $line?->amount);

        $line->refresh();
        $line->recalculateAmounts();
        $line->save();
        $estimate->refreshTotalsFromLines();
        $estimate->save();

        $this->assertSame('30000.00', (string) $line->fresh()->amount);
        $this->assertSame('30000.00', (string) $estimate->fresh()->grand_total);
    }

    public function test_customer_pdf_has_six_columns_and_no_internal_fields(): void
    {
        $estimate = $this->makeEstimate($this->superAdmin());
        $html = app(CustomerEstimateDocument::class)->html($estimate);

        foreach (CustomerEstimateDocument::ITEM_COLUMNS as $column) {
            $this->assertStringContainsString($column, $html);
        }
        $this->assertStringContainsString($estimate->number, $html);
        $this->assertStringContainsString($estimate->estimate_date->format('d M Y'), $html);
        $this->assertStringContainsString($estimate->valid_until->format('d M Y'), $html);
        $this->assertStringNotContainsString('Profit', $html);
        $this->assertStringNotContainsString('Actual cost', $html);
        $this->assertStringNotContainsString('Actual Cost', $html);
        $this->assertStringNotContainsString('Basic Cost', $html);
        $this->assertStringNotContainsString('Estimate Status', $html);
        $this->assertStringNotContainsString('Draft', $html);
        $this->assertStringContainsString('Balaji Royal Events', $html);
        $this->assertStringContainsString('balajievents19@gmail.com', $html);
    }

    public function test_internal_costing_uses_rate_basis_and_is_forbidden_to_lead_managers(): void
    {
        $admin = $this->superAdmin();
        $estimate = $this->makeEstimate($admin, [], [[
            'name' => 'Tent',
            'qty' => 1,
            'rate' => 15000,
            'days' => 2,
            'rate_basis' => EstimateRateBasis::PerDay->value,
            'actual_cost_rate' => 5000,
        ]]);
        $estimate->expenses()->create([
            'type' => EstimateExpenseType::Transport,
            'description' => 'Truck',
            'amount' => 2000,
            'sort_order' => 0,
        ]);
        $estimate->load('expenses');
        $summary = $estimate->fresh(['lines', 'expenses'])->profitSummary();
        $this->assertSame('10000.00', $summary['total_actual_item_cost']);
        $this->assertSame('2000.00', $summary['additional_direct_expenses']);
        $this->assertSame('12000.00', $summary['total_actual_cost']);
        $this->assertSame('18000.00', $summary['profit']);

        $lead = User::factory()->create(['email' => 'lead.estimates@balaji.test']);
        $lead->assignRole(AdminModules::ROLE_LEAD_MANAGER);
        $this->assertFalse($lead->can('viewCosting', $estimate));
        $this->assertFalse($lead->can('view', $estimate));

        $this->actingAs($lead);
        $this->get(route('admin.estimates.pdf', $estimate))->assertForbidden();
    }

    public function test_authorized_admin_can_download_customer_pdf_without_cost_fields(): void
    {
        $admin = $this->superAdmin();
        $estimate = $this->makeEstimate($admin);
        $this->actingAs($admin);
        $response = $this->get(route('admin.estimates.pdf', $estimate));
        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_estimates_permission_is_seeded(): void
    {
        $this->assertTrue(
            Permission::query()
                ->where('name', 'estimates.view')
                ->exists()
        );
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create(['email' => 'estimates.admin@balaji.test']);
        $user->assignRole(AdminModules::ROLE_SUPER_ADMIN);

        return $user;
    }

    /**
     * @param  array<string, mixed>  $header
     * @param  list<array<string, mixed>>  $lines
     */
    private function makeEstimate(User $user, array $header = [], array $lines = []): Estimate
    {
        if ($lines === []) {
            $lines = [[
                'name' => 'Stage décor',
                'qty' => 1,
                'rate' => 15000,
                'days' => 2,
                'rate_basis' => EstimateRateBasis::PerDay->value,
                'actual_cost_rate' => 8000,
            ]];
        }

        return app(EstimatePersister::class)->create(array_merge([
            'customer_name' => 'Test Customer',
            'customer_mobile' => '9462577065',
            'event_name' => 'Wedding',
            'estimate_date' => '2026-10-11',
            'status' => EstimateStatus::Draft->value,
        ], $header), $lines, $user);
    }
}

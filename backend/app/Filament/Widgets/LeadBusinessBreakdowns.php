<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Support\Admin\LeadDashboardMetrics;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class LeadBusinessBreakdowns extends Widget
{
    use InteractsWithPageFilters;

    protected string $view = 'filament.widgets.lead-business-breakdowns';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return ContactInquiryResource::canViewAny();
    }

    /**
     * @return array{
     *     sources: Collection<string, int>,
     *     interests: Collection<int, object>,
     *     assignments: Collection<int, object>
     * }
     */
    public function getBreakdowns(): array
    {
        $metrics = LeadDashboardMetrics::fromFilters($this->pageFilters);

        return [
            'sources' => $metrics->sourceCounts(),
            'interests' => $metrics->interestBreakdown(),
            'assignments' => $metrics->assignmentWorkload(),
        ];
    }
}

<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Models\ContactInquiry;
use App\Support\Admin\LeadDashboardMetrics;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class LeadAttentionLists extends Widget
{
    use InteractsWithPageFilters;

    protected string $view = 'filament.widgets.lead-attention-lists';

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return ContactInquiryResource::canViewAny();
    }

    /**
     * @return array{
     *     recent: Collection<int, ContactInquiry>,
     *     overdue: Collection<int, ContactInquiry>,
     *     upcoming: Collection<int, ContactInquiry>,
     *     leadsUrl: string
     * }
     */
    public function getLists(): array
    {
        $metrics = LeadDashboardMetrics::fromFilters($this->pageFilters);

        return [
            'recent' => $metrics->recentLeads(),
            'overdue' => $metrics->overdueFollowUps(),
            'upcoming' => $metrics->upcomingFollowUps(),
            'leadsUrl' => ContactInquiryResource::getUrl('index'),
        ];
    }

    public function leadUrl(int $id): string
    {
        return ContactInquiryResource::getUrl('edit', ['record' => $id]);
    }
}

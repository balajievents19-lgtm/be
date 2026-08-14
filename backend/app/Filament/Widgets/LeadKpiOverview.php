<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Support\Admin\LeadDashboardMetrics;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LeadKpiOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Lead overview';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return ContactInquiryResource::canViewAny();
    }

    protected function getStats(): array
    {
        $metrics = LeadDashboardMetrics::fromFilters($this->pageFilters);
        $kpis = $metrics->kpis();
        $leadsUrl = ContactInquiryResource::getUrl('index');

        return [
            Stat::make('Total Leads', $kpis['total'])
                ->description('In selected period')
                ->icon(Heroicon::OutlinedInboxArrowDown)
                ->url($leadsUrl),
            Stat::make('New Leads', $kpis['new'])
                ->description('Status: New')
                ->icon(Heroicon::OutlinedSparkles)
                ->color($kpis['new'] > 0 ? 'warning' : 'gray')
                ->url($leadsUrl),
            Stat::make('Follow-ups Scheduled', $kpis['follow_ups_scheduled'])
                ->description('Any Lead with follow-up')
                ->icon(Heroicon::OutlinedCalendarDays)
                ->url($leadsUrl),
            Stat::make('Overdue Follow-ups', $kpis['overdue_follow_ups'])
                ->description('Past due follow-up time')
                ->icon(Heroicon::OutlinedExclamationTriangle)
                ->color($kpis['overdue_follow_ups'] > 0 ? 'danger' : 'success')
                ->url($leadsUrl),
            Stat::make('Won Leads', $kpis['won'])
                ->description('In selected period')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->url($leadsUrl),
            Stat::make('Lost Leads', $kpis['lost'])
                ->description('In selected period')
                ->icon(Heroicon::OutlinedXCircle)
                ->color('danger')
                ->url($leadsUrl),
        ];
    }
}

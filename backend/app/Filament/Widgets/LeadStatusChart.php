<?php

namespace App\Filament\Widgets;

use App\Enums\ContactInquiryStatus;
use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Support\Admin\LeadDashboardMetrics;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class LeadStatusChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Lead status';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    public static function canView(): bool
    {
        return ContactInquiryResource::canViewAny();
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $counts = LeadDashboardMetrics::fromFilters($this->pageFilters)->statusCounts();

        $labels = [];
        $data = [];
        $colors = [];

        foreach (ContactInquiryStatus::cases() as $status) {
            $labels[] = $status->label();
            $data[] = $counts[$status->value] ?? 0;
            $colors[] = match ($status) {
                ContactInquiryStatus::New => '#38bdf8',
                ContactInquiryStatus::Contacted => '#6366f1',
                ContactInquiryStatus::FollowUp => '#f59e0b',
                ContactInquiryStatus::QuotationSent => '#94a3b8',
                ContactInquiryStatus::Won => '#22c55e',
                ContactInquiryStatus::Lost => '#ef4444',
            };
        }

        return [
            'datasets' => [
                [
                    'label' => 'Leads',
                    'data' => $data,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }
}

<?php

namespace App\Filament\Widgets;

use App\Enums\ContactInquiryPriority;
use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Support\Admin\LeadDashboardMetrics;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class LeadPriorityChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Lead priority';

    protected static ?int $sort = 5;

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
        $counts = LeadDashboardMetrics::fromFilters($this->pageFilters)->priorityCounts();

        $labels = [];
        $data = [];
        $colors = [];

        foreach (ContactInquiryPriority::cases() as $priority) {
            $labels[] = $priority->label();
            $data[] = $counts[$priority->value] ?? 0;
            $colors[] = match ($priority) {
                ContactInquiryPriority::Low => '#94a3b8',
                ContactInquiryPriority::Medium => '#f59e0b',
                ContactInquiryPriority::High => '#ef4444',
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

<?php

namespace App\Filament\Pages;

use App\Support\Admin\LeadDashboardMetrics;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?string $navigationLabel = 'Home';

    protected static ?int $navigationSort = -2;

    public function getTitle(): string|Htmlable
    {
        return 'Home';
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Select::make('period')
                            ->label('Period')
                            ->options(LeadDashboardMetrics::periodOptions())
                            ->default(LeadDashboardMetrics::PERIOD_30D)
                            ->native(false),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}

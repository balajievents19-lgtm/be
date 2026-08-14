<?php

namespace App\Filament\Resources\Statistics;

use App\Filament\Clusters\HomeCluster;
use App\Filament\Resources\Statistics\Pages\CreateStatistic;
use App\Filament\Resources\Statistics\Pages\EditStatistic;
use App\Filament\Resources\Statistics\Pages\ListStatistics;
use App\Filament\Resources\Statistics\Schemas\StatisticForm;
use App\Filament\Resources\Statistics\Tables\StatisticsTable;
use App\Models\Statistic;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class StatisticResource extends Resource
{
    protected static ?string $model = Statistic::class;

    protected static ?string $cluster = HomeCluster::class;

    protected static ?string $navigationLabel = 'Statistics';

    protected static ?string $modelLabel = 'Statistic';

    protected static ?string $pluralModelLabel = 'Statistics';

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $recordTitleAttribute = 'label';

    public static function form(Schema $schema): Schema
    {
        return StatisticForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StatisticsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStatistics::route('/'),
            'create' => CreateStatistic::route('/create'),
            'edit' => EditStatistic::route('/{record}/edit'),
        ];
    }
}

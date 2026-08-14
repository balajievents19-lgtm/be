<?php

namespace App\Filament\Resources\EventOverviews;

use App\Filament\Clusters\HomeCluster;
use App\Filament\Resources\EventOverviews\Pages\CreateEventOverview;
use App\Filament\Resources\EventOverviews\Pages\EditEventOverview;
use App\Filament\Resources\EventOverviews\Pages\ListEventOverviews;
use App\Filament\Resources\EventOverviews\Schemas\EventOverviewForm;
use App\Filament\Resources\EventOverviews\Tables\EventOverviewsTable;
use App\Models\EventOverview;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class EventOverviewResource extends Resource
{
    protected static ?string $model = EventOverview::class;

    protected static ?string $cluster = HomeCluster::class;

    protected static ?string $navigationLabel = 'Events Overview';

    protected static ?string $modelLabel = 'Event Overview';

    protected static ?string $pluralModelLabel = 'Events Overview';

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 5;

    /** Kept for content; not part of the owner Website menu list. */
    protected static bool $shouldRegisterNavigation = true;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return EventOverviewForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventOverviewsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEventOverviews::route('/'),
            'create' => CreateEventOverview::route('/create'),
            'edit' => EditEventOverview::route('/{record}/edit'),
        ];
    }
}

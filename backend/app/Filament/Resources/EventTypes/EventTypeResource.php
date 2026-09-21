<?php

namespace App\Filament\Resources\EventTypes;

use App\Filament\Clusters\HomeCluster;
use App\Filament\Resources\EventTypes\Pages\CreateEventType;
use App\Filament\Resources\EventTypes\Pages\EditEventType;
use App\Filament\Resources\EventTypes\Pages\ListEventTypes;
use App\Filament\Resources\EventTypes\Schemas\EventTypeForm;
use App\Filament\Resources\EventTypes\Tables\EventTypesTable;
use App\Models\EventType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class EventTypeResource extends Resource
{
    protected static ?string $model = EventType::class;

    protected static ?string $cluster = HomeCluster::class;

    protected static ?string $navigationLabel = 'Event Types';

    protected static ?string $modelLabel = 'Event Type';

    protected static ?string $pluralModelLabel = 'Event Types';

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return EventTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventTypesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEventTypes::route('/'),
            'create' => CreateEventType::route('/create'),
            'edit' => EditEventType::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

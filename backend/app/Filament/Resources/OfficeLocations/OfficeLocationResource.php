<?php

namespace App\Filament\Resources\OfficeLocations;

use App\Filament\Clusters\ContactCluster;
use App\Filament\Resources\OfficeLocations\Pages\CreateOfficeLocation;
use App\Filament\Resources\OfficeLocations\Pages\EditOfficeLocation;
use App\Filament\Resources\OfficeLocations\Pages\ListOfficeLocations;
use App\Filament\Resources\OfficeLocations\Schemas\OfficeLocationForm;
use App\Filament\Resources\OfficeLocations\Tables\OfficeLocationsTable;
use App\Models\OfficeLocation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class OfficeLocationResource extends Resource
{
    protected static ?string $model = OfficeLocation::class;

    protected static ?string $cluster = ContactCluster::class;

    protected static ?string $navigationLabel = 'Office Locations';

    protected static ?string $modelLabel = 'Office Location';

    protected static ?string $pluralModelLabel = 'Office Locations';

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return OfficeLocationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OfficeLocationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOfficeLocations::route('/'),
            'create' => CreateOfficeLocation::route('/create'),
            'edit' => EditOfficeLocation::route('/{record}/edit'),
        ];
    }
}

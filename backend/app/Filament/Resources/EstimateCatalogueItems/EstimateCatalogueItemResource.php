<?php

namespace App\Filament\Resources\EstimateCatalogueItems;

use App\Filament\Resources\EstimateCatalogueItems\Pages\CreateEstimateCatalogueItem;
use App\Filament\Resources\EstimateCatalogueItems\Pages\EditEstimateCatalogueItem;
use App\Filament\Resources\EstimateCatalogueItems\Pages\ListEstimateCatalogueItems;
use App\Filament\Resources\EstimateCatalogueItems\Schemas\EstimateCatalogueItemForm;
use App\Filament\Resources\EstimateCatalogueItems\Tables\EstimateCatalogueItemsTable;
use App\Models\EstimateCatalogueItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class EstimateCatalogueItemResource extends Resource
{
    protected static ?string $model = EstimateCatalogueItem::class;

    protected static ?string $navigationLabel = 'Item Catalogue';

    protected static ?string $modelLabel = 'Catalogue item';

    protected static ?string $pluralModelLabel = 'Item Catalogue';

    protected static string|UnitEnum|null $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return EstimateCatalogueItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EstimateCatalogueItemsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEstimateCatalogueItems::route('/'),
            'create' => CreateEstimateCatalogueItem::route('/create'),
            'edit' => EditEstimateCatalogueItem::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}

<?php

namespace App\Filament\Resources\GalleryItems;

use App\Filament\Clusters\GalleryCluster;
use App\Filament\Resources\GalleryItems\Pages\CreateGalleryItem;
use App\Filament\Resources\GalleryItems\Pages\EditGalleryItem;
use App\Filament\Resources\GalleryItems\Pages\ListGalleryItems;
use App\Filament\Resources\GalleryItems\Schemas\GalleryItemForm;
use App\Filament\Resources\GalleryItems\Tables\GalleryItemsTable;
use App\Models\GalleryItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class GalleryItemResource extends Resource
{
    protected static ?string $model = GalleryItem::class;

    protected static ?string $cluster = GalleryCluster::class;

    protected static ?string $navigationLabel = 'Images';

    protected static ?string $modelLabel = 'Image';

    protected static ?string $pluralModelLabel = 'Images';

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return GalleryItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GalleryItemsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGalleryItems::route('/'),
            'create' => CreateGalleryItem::route('/create'),
            'edit' => EditGalleryItem::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources\GalleryVideos;

use App\Filament\Clusters\GalleryCluster;
use App\Filament\Resources\GalleryItems\Schemas\GalleryItemForm;
use App\Filament\Resources\GalleryItems\Tables\GalleryItemsTable;
use App\Filament\Resources\GalleryVideos\Pages\CreateGalleryVideo;
use App\Filament\Resources\GalleryVideos\Pages\EditGalleryVideo;
use App\Filament\Resources\GalleryVideos\Pages\ListGalleryVideos;
use App\Models\GalleryItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class GalleryVideoResource extends Resource
{
    protected static ?string $model = GalleryItem::class;

    protected static ?string $cluster = GalleryCluster::class;

    protected static ?string $navigationLabel = 'Videos';

    protected static ?string $modelLabel = 'Video';

    protected static ?string $pluralModelLabel = 'Videos';

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedVideoCamera;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return GalleryItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GalleryItemsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(fn (Builder $query): Builder => $query
                ->whereNotNull('youtube_url')
                ->orWhereNotNull('vimeo_url'));
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGalleryVideos::route('/'),
            'create' => CreateGalleryVideo::route('/create'),
            'edit' => EditGalleryVideo::route('/{record}/edit'),
        ];
    }
}

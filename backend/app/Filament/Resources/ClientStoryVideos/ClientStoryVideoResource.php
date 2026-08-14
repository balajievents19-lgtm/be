<?php

namespace App\Filament\Resources\ClientStoryVideos;

use App\Filament\Clusters\ClientStoriesCluster;
use App\Filament\Resources\ClientStoryVideos\Pages\CreateClientStoryVideo;
use App\Filament\Resources\ClientStoryVideos\Pages\EditClientStoryVideo;
use App\Filament\Resources\ClientStoryVideos\Pages\ListClientStoryVideos;
use App\Filament\Resources\Testimonials\Schemas\TestimonialForm;
use App\Filament\Resources\Testimonials\Tables\TestimonialsTable;
use App\Models\Testimonial;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ClientStoryVideoResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static ?string $cluster = ClientStoriesCluster::class;

    protected static ?string $navigationLabel = 'Videos';

    protected static ?string $modelLabel = 'Video';

    protected static ?string $pluralModelLabel = 'Videos';

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedVideoCamera;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return TestimonialForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TestimonialsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereNotNull('video_url');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClientStoryVideos::route('/'),
            'create' => CreateClientStoryVideo::route('/create'),
            'edit' => EditClientStoryVideo::route('/{record}/edit'),
        ];
    }
}

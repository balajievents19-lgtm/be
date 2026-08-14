<?php

namespace App\Filament\Resources\ClientReviews;

use App\Enums\TestimonialType;
use App\Filament\Clusters\ClientStoriesCluster;
use App\Filament\Resources\ClientReviews\Pages\CreateClientReview;
use App\Filament\Resources\ClientReviews\Pages\EditClientReview;
use App\Filament\Resources\ClientReviews\Pages\ListClientReviews;
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

class ClientReviewResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static ?string $cluster = ClientStoriesCluster::class;

    protected static ?string $navigationLabel = 'Reviews';

    protected static ?string $modelLabel = 'Review';

    protected static ?string $pluralModelLabel = 'Reviews';

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

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
        return parent::getEloquentQuery()->where('type', TestimonialType::ClientSays);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClientReviews::route('/'),
            'create' => CreateClientReview::route('/create'),
            'edit' => EditClientReview::route('/{record}/edit'),
        ];
    }
}

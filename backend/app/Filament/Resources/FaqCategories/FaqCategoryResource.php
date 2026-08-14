<?php

namespace App\Filament\Resources\FaqCategories;

use App\Filament\Clusters\FaqCluster;
use App\Filament\Resources\FaqCategories\Pages\CreateFaqCategory;
use App\Filament\Resources\FaqCategories\Pages\EditFaqCategory;
use App\Filament\Resources\FaqCategories\Pages\ListFaqCategories;
use App\Filament\Resources\FaqCategories\Schemas\FaqCategoryForm;
use App\Filament\Resources\FaqCategories\Tables\FaqCategoriesTable;
use App\Models\FaqCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class FaqCategoryResource extends Resource
{
    protected static ?string $model = FaqCategory::class;

    protected static ?string $cluster = FaqCluster::class;

    protected static ?string $navigationLabel = 'Categories';

    protected static ?string $modelLabel = 'FAQ Category';

    protected static ?string $pluralModelLabel = 'FAQ Categories';

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return FaqCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FaqCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFaqCategories::route('/'),
            'create' => CreateFaqCategory::route('/create'),
            'edit' => EditFaqCategory::route('/{record}/edit'),
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

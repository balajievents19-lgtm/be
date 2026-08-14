<?php

namespace App\Filament\Resources\Redirects;

use App\Filament\Clusters\SeoCluster;
use App\Filament\Resources\Redirects\Pages\CreateRedirect;
use App\Filament\Resources\Redirects\Pages\EditRedirect;
use App\Filament\Resources\Redirects\Pages\ListRedirects;
use App\Filament\Resources\Redirects\Schemas\RedirectForm;
use App\Filament\Resources\Redirects\Tables\RedirectsTable;
use App\Models\Redirect;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RedirectResource extends Resource
{
    protected static ?string $model = Redirect::class;

    protected static ?string $cluster = SeoCluster::class;

    protected static ?string $navigationLabel = 'Redirects';

    protected static ?string $modelLabel = 'Redirect';

    protected static ?string $pluralModelLabel = 'Redirects';

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?string $recordTitleAttribute = 'from_path';

    public static function form(Schema $schema): Schema
    {
        return RedirectForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RedirectsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRedirects::route('/'),
            'create' => CreateRedirect::route('/create'),
            'edit' => EditRedirect::route('/{record}/edit'),
        ];
    }
}

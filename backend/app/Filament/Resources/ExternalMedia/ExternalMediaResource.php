<?php

namespace App\Filament\Resources\ExternalMedia;

use App\Filament\Clusters\GalleryCluster;
use App\Filament\Concerns\DeniesRestrictedStaff;
use App\Filament\Resources\ExternalMedia\Pages\CreateExternalMedia;
use App\Filament\Resources\ExternalMedia\Pages\EditExternalMedia;
use App\Filament\Resources\ExternalMedia\Pages\ListExternalMedia;
use App\Filament\Resources\ExternalMedia\Schemas\ExternalMediaForm;
use App\Filament\Resources\ExternalMedia\Tables\ExternalMediaTable;
use App\Models\ExternalMedia;
use App\Support\Staff\StaffEloquentScope;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ExternalMediaResource extends Resource
{
    use DeniesRestrictedStaff;

    protected static ?string $model = ExternalMedia::class;

    protected static ?string $cluster = GalleryCluster::class;

    protected static ?string $navigationLabel = 'External Media';

    protected static ?string $modelLabel = 'External media';

    protected static ?string $pluralModelLabel = 'External media';

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedVideoCamera;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return ExternalMediaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExternalMediaTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExternalMedia::route('/'),
            'create' => CreateExternalMedia::route('/create'),
            'edit' => EditExternalMedia::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
        $user = Auth::user();

        return $user ? StaffEloquentScope::externalMedia($query, $user) : $query;
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

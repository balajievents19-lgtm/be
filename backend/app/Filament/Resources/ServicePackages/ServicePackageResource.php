<?php

namespace App\Filament\Resources\ServicePackages;

use App\Filament\Clusters\PackagesCluster;
use App\Filament\Resources\ServicePackages\Pages\CreateServicePackage;
use App\Filament\Resources\ServicePackages\Pages\EditServicePackage;
use App\Filament\Resources\ServicePackages\Pages\ListServicePackages;
use App\Filament\Resources\ServicePackages\Schemas\ServicePackageForm;
use App\Filament\Resources\ServicePackages\Tables\ServicePackagesTable;
use App\Models\ServicePackage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ServicePackageResource extends Resource
{
    protected static ?string $model = ServicePackage::class;

    protected static ?string $cluster = PackagesCluster::class;

    protected static ?string $navigationLabel = 'Packages';

    protected static ?string $modelLabel = 'Package';

    protected static ?string $pluralModelLabel = 'Packages';

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ServicePackageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServicePackagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServicePackages::route('/'),
            'create' => CreateServicePackage::route('/create'),
            'edit' => EditServicePackage::route('/{record}/edit'),
        ];
    }
}

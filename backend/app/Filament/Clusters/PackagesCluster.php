<?php

namespace App\Filament\Clusters;

use App\Filament\Concerns\AuthorizesAdminModule;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class PackagesCluster extends Cluster
{
    use AuthorizesAdminModule;

    protected static string $adminModule = 'packages';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Packages';

    protected static ?string $slug = 'packages';
}

<?php

namespace App\Filament\Clusters;

use App\Filament\Concerns\AuthorizesAdminModule;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class BrandCluster extends Cluster
{
    use AuthorizesAdminModule;

    protected static string $adminModule = 'about';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 99;

    protected static ?string $navigationLabel = 'Brand';

    protected static ?string $slug = 'brand';

    protected static bool $shouldRegisterNavigation = false;
}

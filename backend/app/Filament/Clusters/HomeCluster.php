<?php

namespace App\Filament\Clusters;

use App\Filament\Concerns\AuthorizesAdminModule;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class HomeCluster extends Cluster
{
    use AuthorizesAdminModule;

    protected static string $adminModule = 'home';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 0;

    protected static ?string $navigationLabel = 'Home';

    protected static ?string $slug = 'home';
}

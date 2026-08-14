<?php

namespace App\Filament\Clusters;

use App\Filament\Concerns\AuthorizesAdminModule;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class HeaderCluster extends Cluster
{
    use AuthorizesAdminModule;

    protected static string $adminModule = 'header';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBars3CenterLeft;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Header';

    protected static ?string $slug = 'header';
}

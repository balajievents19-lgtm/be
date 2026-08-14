<?php

namespace App\Filament\Clusters;

use App\Filament\Concerns\AuthorizesAdminModule;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class FooterCluster extends Cluster
{
    use AuthorizesAdminModule;

    protected static string $adminModule = 'footer';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBars3BottomLeft;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 11;

    protected static ?string $navigationLabel = 'Footer';

    protected static ?string $slug = 'footer';
}

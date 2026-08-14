<?php

namespace App\Filament\Clusters;

use App\Filament\Concerns\AuthorizesAdminModule;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ServicesCluster extends Cluster
{
    use AuthorizesAdminModule;

    protected static string $adminModule = 'services';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Services';

    protected static ?string $slug = 'services';
}

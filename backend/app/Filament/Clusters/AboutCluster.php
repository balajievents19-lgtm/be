<?php

namespace App\Filament\Clusters;

use App\Filament\Concerns\AuthorizesAdminModule;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class AboutCluster extends Cluster
{
    use AuthorizesAdminModule;

    protected static string $adminModule = 'about';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInformationCircle;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'About';

    protected static ?string $slug = 'about';
}

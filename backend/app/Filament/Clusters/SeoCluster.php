<?php

namespace App\Filament\Clusters;

use App\Filament\Concerns\AuthorizesAdminModule;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class SeoCluster extends Cluster
{
    use AuthorizesAdminModule;

    protected static string $adminModule = 'seo';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlassCircle;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 12;

    protected static ?string $navigationLabel = 'SEO';

    protected static ?string $slug = 'seo';
}

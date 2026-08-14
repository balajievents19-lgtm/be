<?php

namespace App\Filament\Clusters;

use App\Filament\Concerns\AuthorizesAdminModule;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class BlogCluster extends Cluster
{
    use AuthorizesAdminModule;

    protected static string $adminModule = 'blog';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 8;

    protected static ?string $navigationLabel = 'Blog';

    protected static ?string $slug = 'blog';
}

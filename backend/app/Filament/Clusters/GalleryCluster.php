<?php

namespace App\Filament\Clusters;

use App\Filament\Concerns\AuthorizesAdminModule;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class GalleryCluster extends Cluster
{
    use AuthorizesAdminModule;

    protected static string $adminModule = 'gallery';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Gallery';

    protected static ?string $slug = 'gallery';
}

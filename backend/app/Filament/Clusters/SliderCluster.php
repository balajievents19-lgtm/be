<?php

namespace App\Filament\Clusters;

use App\Filament\Concerns\AuthorizesAdminModule;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class SliderCluster extends Cluster
{
    use AuthorizesAdminModule;

    protected static string $adminModule = 'slider';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Slider';

    protected static ?string $slug = 'slider';
}

<?php

namespace App\Filament\Clusters;

use App\Filament\Concerns\AuthorizesAdminModule;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ContactCluster extends Cluster
{
    use AuthorizesAdminModule;

    protected static string $adminModule = 'contact';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Contact';

    protected static ?string $slug = 'contact';
}

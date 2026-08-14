<?php

namespace App\Filament\Clusters;

use App\Filament\Concerns\AuthorizesAdminModule;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class FaqCluster extends Cluster
{
    use AuthorizesAdminModule;

    protected static string $adminModule = 'faq';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 9;

    protected static ?string $navigationLabel = 'FAQ';

    protected static ?string $slug = 'faq';
}

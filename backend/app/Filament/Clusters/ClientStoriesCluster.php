<?php

namespace App\Filament\Clusters;

use App\Filament\Concerns\AuthorizesAdminModule;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ClientStoriesCluster extends Cluster
{
    use AuthorizesAdminModule;

    protected static string $adminModule = 'testimonials';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 7;

    protected static ?string $navigationLabel = 'Testimonials';

    protected static ?string $slug = 'testimonials';
}

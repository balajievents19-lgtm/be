<?php

namespace App\Filament\Pages\Services;

use App\Filament\Clusters\ServicesCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use Filament\Forms\Components\Placeholder;

class ManageServicePricing extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'services';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = ServicesCluster::class;

    protected static ?string $navigationLabel = 'Pricing';

    protected static ?string $title = 'Pricing';

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'pricing';

    protected function formFields(): array
    {
        return [
            Placeholder::make('help')
                ->label('How pricing works')
                ->content('Create and edit package prices under Services → Packages. Each package can show a price label such as “Starting ₹49,999”.'),
        ];
    }
}

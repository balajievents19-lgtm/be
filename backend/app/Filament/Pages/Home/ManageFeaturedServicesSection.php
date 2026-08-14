<?php

namespace App\Filament\Pages\Home;

use App\Filament\Clusters\HomeCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use App\Filament\Resources\Services\ServiceResource;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Support\Icons\Heroicon;

class ManageFeaturedServicesSection extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'home';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = HomeCluster::class;

    protected static ?string $navigationLabel = 'Featured Services';

    protected static ?string $title = 'Featured Services';

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static ?string $slug = 'home-featured-services';

    protected function formFields(): array
    {
        return [
            Placeholder::make('featured_services_help')
                ->label('How this works')
                ->content('The services shown on the homepage are chosen from your services list. Open Services → Services, edit a service, and turn on "Show on Homepage" for each one you want to feature.'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('goToServices')
                ->label('Manage Services')
                ->icon(Heroicon::OutlinedBriefcase)
                ->url(ServiceResource::getUrl('index')),
            ...parent::getHeaderActions(),
        ];
    }
}

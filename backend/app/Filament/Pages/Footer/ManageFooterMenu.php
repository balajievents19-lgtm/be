<?php

namespace App\Filament\Pages\Footer;

use App\Filament\Clusters\FooterCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use App\Filament\Resources\NavigationItems\NavigationItemResource;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Support\Icons\Heroicon;

class ManageFooterMenu extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'footer';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = FooterCluster::class;

    protected static ?string $navigationLabel = 'Footer Menu';

    protected static ?string $title = 'Footer Menu';

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBars3;

    protected static ?string $slug = 'footer-menu';

    protected function formFields(): array
    {
        return [
            Placeholder::make('footer_menu_help')
                ->label('How this works')
                ->content('Footer menu links are managed in the same place as the header menu. Open Navigation, and for each link turn on "Show in Footer" to include it in the footer menu.'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('goToNavigation')
                ->label('Manage Navigation')
                ->icon(Heroicon::OutlinedBars3)
                ->url(NavigationItemResource::getUrl('index')),
            ...parent::getHeaderActions(),
        ];
    }
}

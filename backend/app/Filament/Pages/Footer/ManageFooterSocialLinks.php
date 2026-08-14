<?php

namespace App\Filament\Pages\Footer;

use App\Filament\Clusters\FooterCluster;
use App\Filament\Pages\Brand\ManageBrandSocialMedia;
use App\Filament\Pages\EditWebsiteSettingPage;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Toggle;
use Filament\Support\Icons\Heroicon;

class ManageFooterSocialLinks extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'footer';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = FooterCluster::class;

    protected static ?string $navigationLabel = 'Social Links';

    protected static ?string $title = 'Footer Social Links';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShare;

    protected static ?string $slug = 'footer-social';

    protected function formFields(): array
    {
        return [
            Toggle::make('footer_social_enabled')
                ->label('Show Social Links in Footer')
                ->default(true)
                ->helperText('Your social media links (Facebook, Instagram, etc.) are managed under Brand → Social Media.'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('goToBrandSocial')
                ->label('Edit Social Links')
                ->icon(Heroicon::OutlinedShare)
                ->url(ManageBrandSocialMedia::getUrl()),
            ...parent::getHeaderActions(),
        ];
    }
}

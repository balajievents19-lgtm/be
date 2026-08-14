<?php

namespace App\Filament\Pages\Seo;

use App\Filament\Clusters\SeoCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use App\Filament\Support\WebsitePublishFields;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Toggle;
use Filament\Support\Icons\Heroicon;

class ManageSitemapSettings extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'seo';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = SeoCluster::class;

    protected static ?string $navigationLabel = 'Sitemap';

    protected static ?string $title = 'Sitemap';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static ?string $slug = 'seo-sitemap';

    protected function formFields(): array
    {
        return [
            Toggle::make('sitemap_enabled')
                ->label('Enable Sitemap')
                ->default(true)
                ->helperText('When on, search engines can find a list of all your pages.'),
            Placeholder::make('sitemap_url')
                ->label('Sitemap Address')
                ->content(WebsitePublishFields::previewUrl('/sitemap.xml')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewSitemap')
                ->label('View Sitemap')
                ->icon(Heroicon::OutlinedGlobeAlt)
                ->url(WebsitePublishFields::previewUrl('/sitemap.xml'), shouldOpenInNewTab: true)
                ->color('gray'),
        ];
    }
}

<?php

namespace App\Filament\Pages\Seo;

use App\Filament\Clusters\SeoCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use App\Filament\Resources\Settings\Schemas\SettingForm;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class ManageGlobalSeo extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'seo';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = SeoCluster::class;

    protected static ?string $navigationLabel = 'Global SEO';

    protected static ?string $title = 'Global SEO';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlassCircle;

    protected static ?string $slug = 'seo-global';

    protected function formFields(): array
    {
        return SettingForm::seoFields();
    }
}

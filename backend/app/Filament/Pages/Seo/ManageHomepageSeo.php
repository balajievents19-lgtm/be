<?php

namespace App\Filament\Pages\Seo;

use App\Filament\Clusters\SeoCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use App\Filament\Resources\Settings\Schemas\SettingForm;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class ManageHomepageSeo extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'seo';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = SeoCluster::class;

    protected static ?string $navigationLabel = 'Homepage SEO';

    protected static ?string $title = 'Homepage SEO';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?string $slug = 'seo-homepage';

    protected function formFields(): array
    {
        return SettingForm::homepageSeoFields();
    }
}

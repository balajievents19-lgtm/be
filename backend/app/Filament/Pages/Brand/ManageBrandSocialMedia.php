<?php

namespace App\Filament\Pages\Brand;

use App\Filament\Clusters\FooterCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use App\Filament\Resources\Settings\Schemas\SettingForm;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class ManageBrandSocialMedia extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'footer';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = FooterCluster::class;

    protected static ?string $navigationLabel = 'Social Media';

    protected static ?string $title = 'Social Media';

    protected static ?int $navigationSort = 7;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShare;

    protected static ?string $slug = 'brand-social';

    protected function formFields(): array
    {
        return SettingForm::socialFields();
    }
}

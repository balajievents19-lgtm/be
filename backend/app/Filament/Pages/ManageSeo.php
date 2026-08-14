<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Settings\Schemas\SettingForm;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageSeo extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'seo';

    protected static bool $isDiscovered = true;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'SEO';

    protected static ?string $title = 'SEO';

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 13;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlassCircle;

    protected static ?string $slug = 'website-seo';

    protected function formFields(): array
    {
        return SettingForm::seoFields();
    }
}

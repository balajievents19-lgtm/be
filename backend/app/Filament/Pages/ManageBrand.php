<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Settings\Schemas\SettingForm;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageBrand extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'about';

    protected static bool $isDiscovered = true;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Brand';

    protected static ?string $title = 'Brand';

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $slug = 'website-brand';

    protected function formFields(): array
    {
        return array_merge(
            SettingForm::companyFields(),
            SettingForm::mediaFields(),
        );
    }
}

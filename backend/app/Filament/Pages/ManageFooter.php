<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Settings\Schemas\SettingForm;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageFooter extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'footer';

    protected static bool $isDiscovered = true;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Footer';

    protected static ?string $title = 'Footer';

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 12;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBars3BottomLeft;

    protected static ?string $slug = 'website-footer';

    protected function formFields(): array
    {
        return SettingForm::footerFields();
    }
}

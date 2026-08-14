<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Settings\Schemas\SettingForm;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageHeader extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'header';

    protected static bool $isDiscovered = true;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Header';

    protected static ?string $title = 'Header';

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBars3CenterLeft;

    protected static ?string $slug = 'website-header';

    protected function formFields(): array
    {
        return SettingForm::headerFields();
    }
}

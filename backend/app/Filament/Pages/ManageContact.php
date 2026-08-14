<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Settings\Schemas\SettingForm;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageContact extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'contact';

    protected static bool $isDiscovered = true;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Contact';

    protected static ?string $title = 'Contact';

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 11;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    protected static ?string $slug = 'website-contact';

    protected function previewPath(): string
    {
        return '/contact';
    }

    protected function formFields(): array
    {
        return array_merge(
            SettingForm::contactFields(),
            SettingForm::socialFields(),
            SettingForm::businessFields(),
        );
    }
}

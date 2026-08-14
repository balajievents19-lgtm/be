<?php

namespace App\Filament\Pages\Brand;

use App\Filament\Clusters\ContactCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use App\Filament\Resources\Settings\Schemas\SettingForm;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class ManageBrandContactDetails extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'contact';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = ContactCluster::class;

    protected static ?string $navigationLabel = 'Contact Details';

    protected static ?string $title = 'Contact Details';

    protected static ?int $navigationSort = 6;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    protected static ?string $slug = 'brand-contact';

    protected function previewPath(): string
    {
        return '/contact';
    }

    protected function formFields(): array
    {
        return SettingForm::contactFields();
    }
}

<?php

namespace App\Filament\Pages\Contact;

use App\Filament\Clusters\ContactCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use App\Filament\Resources\Settings\Schemas\SettingForm;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class ManageContactBusinessHours extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'contact';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = ContactCluster::class;

    protected static ?string $navigationLabel = 'Business Hours';

    protected static ?string $title = 'Business Hours';

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $slug = 'contact-hours';

    protected function formFields(): array
    {
        return SettingForm::businessFields();
    }
}

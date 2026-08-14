<?php

namespace App\Filament\Pages\About;

use App\Filament\Clusters\AboutCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use App\Filament\Resources\Settings\Schemas\SettingForm;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class ManageAboutCompany extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'about';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = AboutCluster::class;

    protected static ?string $navigationLabel = 'Company';

    protected static ?string $title = 'About the Company';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static ?string $slug = 'about-company';

    protected function previewPath(): string
    {
        return '/about';
    }

    protected function formFields(): array
    {
        return SettingForm::aboutFields();
    }
}

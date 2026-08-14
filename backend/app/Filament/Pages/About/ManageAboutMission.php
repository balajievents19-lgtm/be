<?php

namespace App\Filament\Pages\About;

use App\Filament\Clusters\AboutCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use App\Filament\Resources\Settings\Schemas\SettingForm;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class ManageAboutMission extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'about';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = AboutCluster::class;

    protected static ?string $navigationLabel = 'Mission';

    protected static ?string $title = 'Our Mission';

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    protected static ?string $slug = 'about-mission';

    protected function previewPath(): string
    {
        return '/about';
    }

    protected function formFields(): array
    {
        return SettingForm::aboutMissionFields();
    }
}

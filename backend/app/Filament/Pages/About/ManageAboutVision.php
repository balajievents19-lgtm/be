<?php

namespace App\Filament\Pages\About;

use App\Filament\Clusters\AboutCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use App\Filament\Resources\Settings\Schemas\SettingForm;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class ManageAboutVision extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'about';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = AboutCluster::class;

    protected static ?string $navigationLabel = 'Vision';

    protected static ?string $title = 'Our Vision';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEye;

    protected static ?string $slug = 'about-vision';

    protected function previewPath(): string
    {
        return '/about';
    }

    protected function formFields(): array
    {
        return SettingForm::aboutVisionFields();
    }
}

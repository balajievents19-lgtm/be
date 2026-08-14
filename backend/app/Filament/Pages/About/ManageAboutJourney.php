<?php

namespace App\Filament\Pages\About;

use App\Filament\Clusters\AboutCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use App\Filament\Resources\Settings\Schemas\SettingForm;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class ManageAboutJourney extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'about';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = AboutCluster::class;

    protected static ?string $navigationLabel = 'Journey';

    protected static ?string $title = 'Our Journey';

    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static ?string $slug = 'about-journey';

    protected function previewPath(): string
    {
        return '/about';
    }

    protected function formFields(): array
    {
        return SettingForm::aboutJourneyFields();
    }
}

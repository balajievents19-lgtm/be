<?php

namespace App\Filament\Pages\Seo;

use App\Filament\Clusters\SeoCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use App\Filament\Resources\Settings\Schemas\SettingForm;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class ManageRobotsSettings extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'seo';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = SeoCluster::class;

    protected static ?string $navigationLabel = 'Robots';

    protected static ?string $title = 'Robots';

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNoSymbol;

    protected static ?string $slug = 'seo-robots';

    protected function formFields(): array
    {
        return SettingForm::robotsExtraFields();
    }
}

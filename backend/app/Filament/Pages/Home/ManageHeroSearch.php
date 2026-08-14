<?php

namespace App\Filament\Pages\Home;

use App\Filament\Clusters\SliderCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use App\Filament\Resources\Settings\Schemas\SettingForm;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class ManageHeroSearch extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'slider';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = SliderCluster::class;

    protected static ?string $navigationLabel = 'Search Box';

    protected static ?string $title = 'Slider Search Box';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;

    protected static ?string $slug = 'slider-search';

    protected function formFields(): array
    {
        return SettingForm::heroSearchFields();
    }
}

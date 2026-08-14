<?php

namespace App\Filament\Pages\Header;

use App\Filament\Clusters\HeaderCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use App\Filament\Resources\Settings\Schemas\SettingForm;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class ManageMobileHeader extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'header';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = HeaderCluster::class;

    protected static ?string $navigationLabel = 'Mobile Header';

    protected static ?string $title = 'Mobile Header';

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDevicePhoneMobile;

    protected static ?string $slug = 'header-mobile';

    protected function formFields(): array
    {
        return SettingForm::mobileHeaderFields();
    }
}

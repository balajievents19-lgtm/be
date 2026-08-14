<?php

namespace App\Filament\Pages\Brand;

use App\Filament\Clusters\HeaderCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Support\Icons\Heroicon;

class ManageFavicon extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'header';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = HeaderCluster::class;

    protected static ?string $navigationLabel = 'Favicon';

    protected static ?string $title = 'Favicon';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static ?string $slug = 'brand-favicon';

    protected function formFields(): array
    {
        return [
            FileUpload::make('favicon')
                ->label('Favicon')
                ->helperText('The small icon shown in browser tabs. Square images work best.')
                ->image()
                ->disk('public')
                ->directory('settings/brand')
                ->visibility('public')
                ->acceptedFileTypes(['image/png', 'image/x-icon', 'image/vnd.microsoft.icon', 'image/jpeg', 'image/svg+xml', 'image/webp'])
                ->maxSize(512),
        ];
    }
}

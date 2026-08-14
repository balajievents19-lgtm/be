<?php

namespace App\Filament\Pages\Brand;

use App\Filament\Clusters\HeaderCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Support\Icons\Heroicon;

class ManageLogo extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'header';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = HeaderCluster::class;

    protected static ?string $navigationLabel = 'Logo';

    protected static ?string $title = 'Logo';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $slug = 'brand-logo';

    protected function formFields(): array
    {
        return [
            FileUpload::make('logo')
                ->label('Logo')
                ->helperText('Shown in the header of the website.')
                ->image()
                ->disk('public')
                ->directory('settings/brand')
                ->visibility('public')
                ->imageEditor()
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'])
                ->maxSize(2048),
            FileUpload::make('dark_logo')
                ->label('Dark Logo')
                ->helperText('Used on dark backgrounds, if your header supports it.')
                ->image()
                ->disk('public')
                ->directory('settings/brand')
                ->visibility('public')
                ->imageEditor()
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'])
                ->maxSize(2048),
            FileUpload::make('footer_logo')
                ->label('Footer Logo')
                ->helperText('Shown in the website footer.')
                ->image()
                ->disk('public')
                ->directory('settings/brand')
                ->visibility('public')
                ->imageEditor()
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'])
                ->maxSize(2048),
        ];
    }
}

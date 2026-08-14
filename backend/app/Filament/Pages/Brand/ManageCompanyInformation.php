<?php

namespace App\Filament\Pages\Brand;

use App\Filament\Clusters\AboutCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Support\Icons\Heroicon;

class ManageCompanyInformation extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'about';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = AboutCluster::class;

    protected static ?string $navigationLabel = 'Company Information';

    protected static ?string $title = 'Company Information';

    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static ?string $slug = 'brand-company';

    protected function previewPath(): string
    {
        return '/about';
    }

    protected function formFields(): array
    {
        return [
            Textarea::make('company_description')
                ->label('Company Description')
                ->helperText('A short summary of your company, used across the website.')
                ->rows(4)
                ->columnSpanFull(),
            FileUpload::make('about_image')
                ->label('About Image')
                ->helperText('Optional. Shown on the About page.')
                ->image()
                ->disk('public')
                ->directory('settings/about')
                ->visibility('public')
                ->imageEditor()
                ->maxSize(5120)
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                ->columnSpanFull(),
        ];
    }
}

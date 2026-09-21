<?php

namespace App\Filament\Pages\Brand;

use App\Filament\Clusters\AboutCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;

class ManageBrandName extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'about';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = AboutCluster::class;

    protected static ?string $navigationLabel = 'Company Name';

    protected static ?string $title = 'Company Name';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static ?string $slug = 'brand-name';

    protected function formFields(): array
    {
        return [
            TextInput::make('company_name')
                ->label('Company Name')
                ->required()
                ->maxLength(255)
                ->helperText('Public brand name (Balaji Royal Events). Do not change the logo file unless replacing an approved asset.'),
        ];
    }
}

<?php

namespace App\Filament\Pages\Brand;

use App\Filament\Clusters\AboutCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;

class ManageTagline extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'about';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = AboutCluster::class;

    protected static ?string $navigationLabel = 'Tagline';

    protected static ?string $title = 'Tagline';

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleBottomCenterText;

    protected static ?string $slug = 'brand-tagline';

    protected function formFields(): array
    {
        return [
            TextInput::make('company_tagline')
                ->label('Tagline')
                ->helperText('A short phrase shown alongside your brand name.')
                ->maxLength(255),
        ];
    }
}

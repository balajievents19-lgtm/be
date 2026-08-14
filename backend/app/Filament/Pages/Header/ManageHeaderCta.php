<?php

namespace App\Filament\Pages\Header;

use App\Filament\Clusters\HeaderCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;

class ManageHeaderCta extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'header';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = HeaderCluster::class;

    protected static ?string $navigationLabel = 'CTA Button';

    protected static ?string $title = 'Header CTA Button';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCursorArrowRays;

    protected static ?string $slug = 'header-cta';

    protected function formFields(): array
    {
        return [
            TextInput::make('header_cta_label')
                ->label('Button Label')
                ->maxLength(100),
            TextInput::make('header_cta_url')
                ->label('Button Link')
                ->maxLength(255)
                ->helperText('Example: /contact or https://...'),
        ];
    }
}

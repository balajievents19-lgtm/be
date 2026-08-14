<?php

namespace App\Filament\Pages\Header;

use App\Filament\Clusters\HeaderCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Support\Icons\Heroicon;

class ManageTopBar extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'header';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = HeaderCluster::class;

    protected static ?string $navigationLabel = 'Top Bar';

    protected static ?string $title = 'Top Bar';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBars3BottomLeft;

    protected static ?string $slug = 'header-top-bar';

    protected function formFields(): array
    {
        return [
            Toggle::make('header_enabled')
                ->label('Enable Header')
                ->default(true),
            Toggle::make('top_bar_enabled')
                ->label('Enable Top Bar')
                ->default(true),
            TextInput::make('top_bar_text')
                ->label('Top Bar Text')
                ->helperText('Short message shown in the thin bar above the header.')
                ->maxLength(255),
        ];
    }
}

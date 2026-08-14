<?php

namespace App\Filament\Pages\Header;

use App\Filament\Clusters\HeaderCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use BackedEnum;
use Filament\Forms\Components\Toggle;
use Filament\Support\Icons\Heroicon;

class ManageStickyHeader extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'header';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = HeaderCluster::class;

    protected static ?string $navigationLabel = 'Sticky Header';

    protected static ?string $title = 'Sticky Header';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpOnSquare;

    protected static ?string $slug = 'header-sticky';

    protected function formFields(): array
    {
        return [
            Toggle::make('sticky_header_enabled')
                ->label('Keep Header Visible When Scrolling')
                ->default(true),
        ];
    }
}

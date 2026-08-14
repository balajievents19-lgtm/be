<?php

namespace App\Filament\Pages\Footer;

use App\Filament\Clusters\FooterCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Support\Icons\Heroicon;

class ManageFooterCopyright extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'footer';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = FooterCluster::class;

    protected static ?string $navigationLabel = 'Copyright';

    protected static ?string $title = 'Footer & Copyright';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $slug = 'footer-copyright';

    protected function formFields(): array
    {
        return [
            Toggle::make('footer_enabled')
                ->label('Enable Footer')
                ->default(true),
            Textarea::make('footer_about')
                ->label('Footer About Text')
                ->rows(4)
                ->columnSpanFull(),
            TextInput::make('copyright_text')
                ->label('Copyright Text')
                ->maxLength(255),
        ];
    }
}

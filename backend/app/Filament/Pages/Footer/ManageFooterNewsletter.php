<?php

namespace App\Filament\Pages\Footer;

use App\Filament\Clusters\FooterCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use BackedEnum;
use Filament\Forms\Components\Toggle;
use Filament\Support\Icons\Heroicon;

class ManageFooterNewsletter extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'footer';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = FooterCluster::class;

    protected static ?string $navigationLabel = 'Newsletter';

    protected static ?string $title = 'Footer Newsletter';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $slug = 'footer-newsletter';

    protected function formFields(): array
    {
        return [
            Toggle::make('footer_newsletter_enabled')
                ->label('Show Newsletter Signup in Footer')
                ->default(true),
        ];
    }
}

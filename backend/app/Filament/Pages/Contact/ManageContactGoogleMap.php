<?php

namespace App\Filament\Pages\Contact;

use App\Filament\Clusters\ContactCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Support\Icons\Heroicon;

class ManageContactGoogleMap extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'contact';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = ContactCluster::class;

    protected static ?string $navigationLabel = 'Google Map';

    protected static ?string $title = 'Google Map';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?string $slug = 'contact-map';

    protected function previewPath(): string
    {
        return '/contact';
    }

    protected function formFields(): array
    {
        return [
            Textarea::make('google_map_embed')
                ->label('Google Map Embed')
                ->rows(4)
                ->helperText('Paste the Google Maps iframe embed HTML.')
                ->columnSpanFull(),
        ];
    }
}

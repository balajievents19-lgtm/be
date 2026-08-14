<?php

namespace App\Filament\Pages\Home;

use App\Filament\Clusters\HomeCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use App\Filament\Resources\GalleryItems\GalleryItemResource;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Support\Icons\Heroicon;

class ManageFeaturedGallerySection extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'home';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = HomeCluster::class;

    protected static ?string $navigationLabel = 'Featured Gallery';

    protected static ?string $title = 'Featured Gallery';

    protected static ?int $navigationSort = 6;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $slug = 'home-featured-gallery';

    protected function formFields(): array
    {
        return [
            Placeholder::make('featured_gallery_help')
                ->label('How this works')
                ->content('The gallery images shown on the homepage are chosen from your gallery. Open Gallery → Images, edit an image, and turn on "Show on Homepage" for each one you want to feature.'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('goToGallery')
                ->label('Manage Gallery')
                ->icon(Heroicon::OutlinedSquares2x2)
                ->url(GalleryItemResource::getUrl('index')),
            ...parent::getHeaderActions(),
        ];
    }
}

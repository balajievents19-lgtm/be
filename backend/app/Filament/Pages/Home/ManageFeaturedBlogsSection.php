<?php

namespace App\Filament\Pages\Home;

use App\Filament\Clusters\HomeCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use App\Filament\Resources\BlogPosts\BlogPostResource;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Support\Icons\Heroicon;

class ManageFeaturedBlogsSection extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'home';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = HomeCluster::class;

    protected static ?string $navigationLabel = 'Featured Blogs';

    protected static ?string $title = 'Featured Blog Posts';

    protected static ?int $navigationSort = 9;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static ?string $slug = 'home-featured-blogs';

    protected function formFields(): array
    {
        return [
            Placeholder::make('featured_blogs_help')
                ->label('How this works')
                ->content('The blog posts shown on the homepage are chosen from your Blog articles. Open Blog → Articles, edit a post, and turn on "Show on Homepage" for each one you want to feature.'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('goToBlog')
                ->label('Manage Blog Posts')
                ->icon(Heroicon::OutlinedNewspaper)
                ->url(BlogPostResource::getUrl('index')),
            ...parent::getHeaderActions(),
        ];
    }
}

<?php

namespace App\Filament\Pages\Home;

use App\Filament\Clusters\HomeCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use App\Filament\Resources\Testimonials\TestimonialResource;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Support\Icons\Heroicon;

class ManageFeaturedClientStoriesSection extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'home';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = HomeCluster::class;

    protected static ?string $navigationLabel = 'Featured Testimonials';

    protected static ?string $title = 'Featured Client Stories';

    protected static ?int $navigationSort = 7;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $slug = 'home-featured-stories';

    protected function formFields(): array
    {
        return [
            Placeholder::make('featured_stories_help')
                ->label('How this works')
                ->content('The client stories and testimonials shown on the homepage are chosen from Client Stories. Edit a story or review and turn on "Show on Homepage" for each one you want to feature.'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('goToClientStories')
                ->label('Manage Client Stories')
                ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                ->url(TestimonialResource::getUrl('index')),
            ...parent::getHeaderActions(),
        ];
    }
}

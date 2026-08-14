<?php

namespace App\Filament\Pages\ClientStories;

use App\Filament\Clusters\ClientStoriesCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use Filament\Forms\Components\Placeholder;

class ManageClientRatings extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'testimonials';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = ClientStoriesCluster::class;

    protected static ?string $navigationLabel = 'Ratings';

    protected static ?string $title = 'Ratings';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'ratings';

    protected function formFields(): array
    {
        return [
            Placeholder::make('help')
                ->label('How ratings work')
                ->content('Set a star rating (1–5) when editing a review under Our Client Stories → Reviews. Ratings appear with Google-style client feedback.'),
        ];
    }
}

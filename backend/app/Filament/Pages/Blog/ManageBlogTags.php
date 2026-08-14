<?php

namespace App\Filament\Pages\Blog;

use App\Filament\Clusters\BlogCluster;
use App\Filament\Pages\EditWebsiteSettingPage;
use Filament\Forms\Components\Placeholder;

class ManageBlogTags extends EditWebsiteSettingPage
{
    protected static string $adminModule = 'blog';

    protected static bool $isDiscovered = true;

    protected static ?string $cluster = BlogCluster::class;

    protected static ?string $navigationLabel = 'Tags';

    protected static ?string $title = 'Tags';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'tags';

    protected function formFields(): array
    {
        return [
            Placeholder::make('help')
                ->label('How tags work')
                ->content('Add tags while editing each article under Blog → Articles. Tags help visitors find related wedding and event stories.'),
        ];
    }
}

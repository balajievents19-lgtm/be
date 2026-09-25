<?php

namespace App\Filament\Resources\BlogPosts\Pages;

use App\Filament\Resources\BlogPosts\BlogPostResource;
use App\Filament\Concerns\AppliesStaffContentRules;
use Filament\Resources\Pages\CreateRecord;

class CreateBlogPost extends CreateRecord
{
    use AppliesStaffContentRules;

    protected static string $resource = BlogPostResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->applyStaffCreateData($data);
    }

    protected function afterCreate(): void
    {
        $this->notifyModerationIfNeeded($this->getRecord());
    }
}

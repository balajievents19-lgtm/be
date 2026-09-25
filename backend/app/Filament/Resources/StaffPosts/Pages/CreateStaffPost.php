<?php

namespace App\Filament\Resources\StaffPosts\Pages;

use App\Filament\Concerns\AppliesStaffContentRules;
use App\Filament\Resources\StaffPosts\StaffPostResource;
use App\Support\ContentCache;
use Filament\Resources\Pages\CreateRecord;

class CreateStaffPost extends CreateRecord
{
    use AppliesStaffContentRules;

    protected static string $resource = StaffPostResource::class;

    protected static ?string $title = 'Create Post';

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
        ContentCache::flush(ContentCache::GALLERY, ContentCache::GALLERY_CATEGORIES, ContentCache::SERVICES);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

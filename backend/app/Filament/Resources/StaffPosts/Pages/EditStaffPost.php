<?php

namespace App\Filament\Resources\StaffPosts\Pages;

use App\Filament\Concerns\AppliesStaffContentRules;
use App\Filament\Resources\StaffPosts\StaffPostResource;
use App\Support\ContentCache;
use Filament\Resources\Pages\EditRecord;

class EditStaffPost extends EditRecord
{
    use AppliesStaffContentRules;

    protected static string $resource = StaffPostResource::class;

    protected static ?string $title = 'Edit Post';

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->applyStaffUpdateData($data, $this->getRecord());
    }

    protected function afterSave(): void
    {
        $this->notifyModerationIfNeeded($this->getRecord());
        ContentCache::flush(ContentCache::GALLERY, ContentCache::GALLERY_CATEGORIES, ContentCache::SERVICES);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}

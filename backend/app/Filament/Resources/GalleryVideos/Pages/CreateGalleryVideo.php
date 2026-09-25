<?php

namespace App\Filament\Resources\GalleryVideos\Pages;

use App\Filament\Resources\GalleryVideos\GalleryVideoResource;
use App\Filament\Concerns\AppliesStaffContentRules;
use Filament\Resources\Pages\CreateRecord;

class CreateGalleryVideo extends CreateRecord
{
    use AppliesStaffContentRules;

    protected static string $resource = GalleryVideoResource::class;

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

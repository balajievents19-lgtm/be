<?php

namespace App\Filament\Resources\GalleryVideos\Pages;

use App\Filament\Resources\GalleryVideos\GalleryVideoResource;
use App\Filament\Concerns\AppliesStaffContentRules;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditGalleryVideo extends EditRecord
{
    use AppliesStaffContentRules;

    protected static string $resource = GalleryVideoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->visible(fn (): bool => $this->staffCanDelete()),
            ForceDeleteAction::make()->visible(fn (): bool => $this->staffCanDelete()),
            RestoreAction::make()->visible(fn (): bool => $this->staffCanDelete()),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->applyStaffUpdateData($data, $this->getRecord());
    }
}

<?php

namespace App\Filament\Resources\ExternalMedia\Pages;

use App\Filament\Resources\ExternalMedia\ExternalMediaResource;
use App\Filament\Concerns\AppliesStaffContentRules;
use Filament\Resources\Pages\CreateRecord;

class CreateExternalMedia extends CreateRecord
{
    use AppliesStaffContentRules;

    protected static string $resource = ExternalMediaResource::class;

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

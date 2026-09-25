<?php

namespace App\Filament\Resources\BlogPosts\Pages;

use App\Filament\Resources\BlogPosts\BlogPostResource;
use App\Filament\Concerns\AppliesStaffContentRules;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditBlogPost extends EditRecord
{
    use AppliesStaffContentRules;

    protected static string $resource = BlogPostResource::class;

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

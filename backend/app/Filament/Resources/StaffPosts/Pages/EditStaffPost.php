<?php

namespace App\Filament\Resources\StaffPosts\Pages;

use App\Filament\Concerns\AppliesStaffContentRules;
use App\Filament\Resources\StaffPosts\StaffPostResource;
use App\Support\ContentCache;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditStaffPost extends EditRecord
{
    use AppliesStaffContentRules;

    protected static string $resource = StaffPostResource::class;

    protected static ?string $title = 'Edit Post';

    public bool $submitForReview = false;

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

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [];
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('saveDraft')
                ->label('Save Draft')
                ->color('gray')
                ->action('saveDraft'),
            Action::make('submitForReview')
                ->label('Resubmit for Review')
                ->action('submitPostForReview'),
        ];
    }

    public function saveDraft(): void
    {
        $this->submitForReview = false;
        $this->save();
    }

    public function submitPostForReview(): void
    {
        $this->submitForReview = true;
        $this->save();
    }
}

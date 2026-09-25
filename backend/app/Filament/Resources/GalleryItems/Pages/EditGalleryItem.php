<?php

namespace App\Filament\Resources\GalleryItems\Pages;

use App\Enums\ContentModerationStatus;
use App\Filament\Concerns\AppliesStaffContentRules;
use App\Filament\Resources\GalleryItems\GalleryItemResource;
use App\Support\ContentCache;
use App\Support\Staff\ContentModeration;
use App\Support\Staff\StaffContentAccess;
use App\Support\Staff\WhatsAppShare;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditGalleryItem extends EditRecord
{
    use AppliesStaffContentRules;

    protected static string $resource = GalleryItemResource::class;

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();
        $inQueue = in_array((string) $record->moderation_status, ContentModerationStatus::reviewQueueValues(), true);
        $published = $record->isPubliclyVisible();
        $canApprove = StaffContentAccess::canApprove(Auth::user());

        return [
            Action::make('approvePublish')
                ->label('Approve & Publish')
                ->color('success')
                ->visible(fn (): bool => $canApprove && $inQueue)
                ->requiresConfirmation()
                ->action(function (): void {
                    ContentModeration::approve(Auth::user(), $this->getRecord());
                    ContentCache::flush(ContentCache::GALLERY, ContentCache::GALLERY_CATEGORIES, ContentCache::SERVICES);
                    Notification::make()->title('Approved and published')->success()->send();
                    $this->refreshFormData(['moderation_status', 'status', 'reviewed_by', 'reviewed_at']);
                }),
            Action::make('reject')
                ->label('Reject')
                ->color('danger')
                ->visible(fn (): bool => $canApprove && $inQueue)
                ->form([
                    Textarea::make('notes')
                        ->label('Rejection reason')
                        ->required()
                        ->maxLength(500)
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    ContentModeration::reject(Auth::user(), $this->getRecord(), $data['notes'] ?? null);
                    ContentCache::flush(ContentCache::GALLERY, ContentCache::GALLERY_CATEGORIES, ContentCache::SERVICES);
                    Notification::make()->title('Rejected')->danger()->send();
                    $this->refreshFormData(['moderation_status', 'status', 'moderation_notes', 'reviewed_by', 'reviewed_at']);
                }),
            Action::make('unpublish')
                ->label('Unpublish')
                ->color('warning')
                ->visible(fn (): bool => $canApprove && $published)
                ->requiresConfirmation()
                ->action(function (): void {
                    ContentModeration::unpublish(Auth::user(), $this->getRecord());
                    ContentCache::flush(ContentCache::GALLERY, ContentCache::GALLERY_CATEGORIES, ContentCache::SERVICES);
                    Notification::make()->title('Unpublished')->warning()->send();
                    $this->refreshFormData(['moderation_status', 'status']);
                }),
            Action::make('whatsapp')
                ->label('Share on WhatsApp')
                ->url(fn (): string => WhatsAppShare::url($this->getRecord()), shouldOpenInNewTab: true)
                ->visible(fn (): bool => $this->getRecord()->isPubliclyVisible()),
            DeleteAction::make()
                ->visible(fn (): bool => $this->staffCanDelete())
                ->after(fn () => ContentCache::flush(ContentCache::GALLERY, ContentCache::GALLERY_CATEGORIES, ContentCache::SERVICES)),
            ForceDeleteAction::make()
                ->visible(fn (): bool => $this->staffCanDelete())
                ->after(fn () => ContentCache::flush(ContentCache::GALLERY, ContentCache::GALLERY_CATEGORIES, ContentCache::SERVICES)),
            RestoreAction::make()
                ->visible(fn (): bool => $this->staffCanDelete())
                ->after(fn () => ContentCache::flush(ContentCache::GALLERY, ContentCache::GALLERY_CATEGORIES, ContentCache::SERVICES)),
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

    protected function afterSave(): void
    {
        $this->notifyModerationIfNeeded($this->getRecord());
        ContentCache::flush(ContentCache::GALLERY, ContentCache::GALLERY_CATEGORIES, ContentCache::SERVICES);
    }
}

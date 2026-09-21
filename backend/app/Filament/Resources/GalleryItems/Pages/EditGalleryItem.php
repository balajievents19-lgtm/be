<?php

namespace App\Filament\Resources\GalleryItems\Pages;

use App\Filament\Resources\GalleryItems\GalleryItemResource;
use App\Support\ContentCache;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditGalleryItem extends EditRecord
{
    protected static string $resource = GalleryItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->after(fn () => ContentCache::flush(ContentCache::GALLERY, ContentCache::GALLERY_CATEGORIES, ContentCache::SERVICES)),
            ForceDeleteAction::make()
                ->after(fn () => ContentCache::flush(ContentCache::GALLERY, ContentCache::GALLERY_CATEGORIES, ContentCache::SERVICES)),
            RestoreAction::make()
                ->after(fn () => ContentCache::flush(ContentCache::GALLERY, ContentCache::GALLERY_CATEGORIES, ContentCache::SERVICES)),
        ];
    }

    protected function afterSave(): void
    {
        ContentCache::flush(ContentCache::GALLERY, ContentCache::GALLERY_CATEGORIES, ContentCache::SERVICES);
    }
}

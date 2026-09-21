<?php

namespace App\Filament\Resources\GalleryItems\Pages;

use App\Filament\Resources\GalleryItems\GalleryItemResource;
use App\Support\ContentCache;
use Filament\Resources\Pages\CreateRecord;

class CreateGalleryItem extends CreateRecord
{
    protected static string $resource = GalleryItemResource::class;

    protected function afterCreate(): void
    {
        ContentCache::flush(ContentCache::GALLERY, ContentCache::GALLERY_CATEGORIES, ContentCache::SERVICES);
    }
}

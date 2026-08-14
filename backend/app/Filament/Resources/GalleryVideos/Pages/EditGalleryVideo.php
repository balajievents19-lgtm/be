<?php

namespace App\Filament\Resources\GalleryVideos\Pages;

use App\Filament\Resources\GalleryVideos\GalleryVideoResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditGalleryVideo extends EditRecord
{
    protected static string $resource = GalleryVideoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}

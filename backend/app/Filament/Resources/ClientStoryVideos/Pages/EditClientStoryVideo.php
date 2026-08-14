<?php

namespace App\Filament\Resources\ClientStoryVideos\Pages;

use App\Filament\Resources\ClientStoryVideos\ClientStoryVideoResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditClientStoryVideo extends EditRecord
{
    protected static string $resource = ClientStoryVideoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}

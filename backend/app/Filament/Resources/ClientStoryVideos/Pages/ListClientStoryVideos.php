<?php

namespace App\Filament\Resources\ClientStoryVideos\Pages;

use App\Filament\Resources\ClientStoryVideos\ClientStoryVideoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClientStoryVideos extends ListRecords
{
    protected static string $resource = ClientStoryVideoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

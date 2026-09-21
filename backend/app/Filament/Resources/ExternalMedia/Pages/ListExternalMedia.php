<?php

namespace App\Filament\Resources\ExternalMedia\Pages;

use App\Filament\Resources\ExternalMedia\ExternalMediaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExternalMedia extends ListRecords
{
    protected static string $resource = ExternalMediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

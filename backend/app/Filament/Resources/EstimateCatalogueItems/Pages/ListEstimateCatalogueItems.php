<?php

namespace App\Filament\Resources\EstimateCatalogueItems\Pages;

use App\Filament\Resources\EstimateCatalogueItems\EstimateCatalogueItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEstimateCatalogueItems extends ListRecords
{
    protected static string $resource = EstimateCatalogueItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

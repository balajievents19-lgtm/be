<?php

namespace App\Filament\Resources\CtaSections\Pages;

use App\Filament\Resources\CtaSections\CtaSectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCtaSections extends ListRecords
{
    protected static string $resource = CtaSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

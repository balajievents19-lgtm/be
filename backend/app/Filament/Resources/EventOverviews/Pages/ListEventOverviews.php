<?php

namespace App\Filament\Resources\EventOverviews\Pages;

use App\Filament\Resources\EventOverviews\EventOverviewResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEventOverviews extends ListRecords
{
    protected static string $resource = EventOverviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

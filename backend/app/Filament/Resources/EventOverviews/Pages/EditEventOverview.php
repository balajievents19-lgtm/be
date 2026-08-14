<?php

namespace App\Filament\Resources\EventOverviews\Pages;

use App\Filament\Resources\EventOverviews\EventOverviewResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEventOverview extends EditRecord
{
    protected static string $resource = EventOverviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

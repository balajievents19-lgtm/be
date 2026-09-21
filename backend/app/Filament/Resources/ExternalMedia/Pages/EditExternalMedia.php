<?php

namespace App\Filament\Resources\ExternalMedia\Pages;

use App\Filament\Resources\ExternalMedia\ExternalMediaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditExternalMedia extends EditRecord
{
    protected static string $resource = ExternalMediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}

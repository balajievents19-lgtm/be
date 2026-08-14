<?php

namespace App\Filament\Resources\CtaSections\Pages;

use App\Filament\Resources\CtaSections\CtaSectionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCtaSection extends EditRecord
{
    protected static string $resource = CtaSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}

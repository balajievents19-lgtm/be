<?php

namespace App\Filament\Resources\EstimateCatalogueItems\Pages;

use App\Filament\Resources\EstimateCatalogueItems\EstimateCatalogueItemResource;
use App\Models\EstimateCatalogueItem;
use App\Support\Estimates\EstimateCatalogueWriter;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditEstimateCatalogueItem extends EditRecord
{
    protected static string $resource = EstimateCatalogueItemResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var EstimateCatalogueItem $record */
        return app(EstimateCatalogueWriter::class)->save($data, $record);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

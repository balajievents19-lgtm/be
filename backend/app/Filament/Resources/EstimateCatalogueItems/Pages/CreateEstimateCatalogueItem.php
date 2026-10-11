<?php

namespace App\Filament\Resources\EstimateCatalogueItems\Pages;

use App\Filament\Resources\EstimateCatalogueItems\EstimateCatalogueItemResource;
use App\Support\Estimates\EstimateCatalogueWriter;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateEstimateCatalogueItem extends CreateRecord
{
    protected static string $resource = EstimateCatalogueItemResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(EstimateCatalogueWriter::class)->save($data);
    }
}

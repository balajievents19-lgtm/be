<?php

namespace App\Filament\Resources\Estimates\Pages;

use App\Filament\Resources\Estimates\EstimateResource;
use App\Models\Estimate;
use App\Support\Estimates\EstimatePersister;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class EditEstimate extends EditRecord
{
    protected static string $resource = EstimateResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Estimate $record */
        $record = $this->getRecord();
        $data['lines'] = $record->lines->map(fn ($line): array => [
            'catalogue_item_id' => $line->catalogue_item_id,
            'name' => $line->name,
            'description' => $line->description,
            'qty' => $line->qty,
            'rate' => $line->rate,
            'days' => $line->days,
            'rate_basis' => $line->rate_basis?->value,
            'amount' => $line->amount,
            'actual_cost_rate' => $line->actual_cost_rate,
        ])->all();

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $lines = $data['lines'] ?? [];
        unset($data['lines']);

        /** @var Estimate $record */
        return app(EstimatePersister::class)->update($record, $data, $lines, Auth::user());
    }

    protected function getHeaderActions(): array
    {
        /** @var Estimate $record */
        $record = $this->getRecord();

        return [
            Action::make('pdf')
                ->label('Customer PDF')
                ->url(route('admin.estimates.pdf', $record))
                ->openUrlInNewTab(),
            Action::make('costing')
                ->label('Internal costing')
                ->url(EstimateResource::getUrl('costing', ['record' => $record]))
                ->visible(fn (): bool => Auth::user()?->can('viewCosting', $record) ?? false),
            Action::make('duplicate')
                ->requiresConfirmation()
                ->action(function (): void {
                    $copy = app(EstimatePersister::class)->duplicate($this->getRecord(), Auth::user());
                    $this->redirect(EstimateResource::getUrl('edit', ['record' => $copy]));
                }),
            DeleteAction::make(),
        ];
    }
}

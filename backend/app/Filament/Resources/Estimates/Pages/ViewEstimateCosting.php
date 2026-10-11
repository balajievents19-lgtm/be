<?php

namespace App\Filament\Resources\Estimates\Pages;

use App\Filament\Resources\Estimates\EstimateResource;
use App\Filament\Resources\Estimates\Schemas\EstimateCostingForm;
use App\Models\Estimate;
use App\Models\EstimateLine;
use App\Support\Estimates\EstimateAuditor;
use App\Support\Estimates\EstimateMoney;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ViewEstimateCosting extends EditRecord
{
    protected static string $resource = EstimateResource::class;

    public static function getNavigationLabel(): string
    {
        return 'Internal costing';
    }

    public function getTitle(): string
    {
        /** @var Estimate $record */
        $record = $this->getRecord();

        return 'Internal costing — '.$record->number;
    }

    public static function canAccess(array $parameters = []): bool
    {
        $record = $parameters['record'] ?? null;
        $user = Auth::user();
        if (! $user || ! $record instanceof Estimate) {
            return false;
        }

        return $user->can('viewCosting', $record);
    }

    public function form(Schema $schema): Schema
    {
        return EstimateCostingForm::configure($schema);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Estimate $record */
        $record = $this->getRecord();
        $data['lines'] = $record->lines->map(fn (EstimateLine $line): array => [
            'id' => $line->id,
            'name' => $line->name,
            'qty' => $line->qty,
            'rate' => $line->rate,
            'days' => $line->days,
            'rate_basis' => $line->rate_basis?->value,
            'amount' => $line->amount,
            'actual_cost_rate' => $line->actual_cost_rate,
        ])->all();
        $data['expenses'] = $record->expenses->map(fn ($expense): array => $expense->only([
            'type',
            'description',
            'amount',
        ]))->all();

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Estimate $record */
        $lines = $data['lines'] ?? [];
        foreach ($lines as $lineData) {
            $line = $record->lines()->whereKey($lineData['id'] ?? 0)->first();
            if (! $line) {
                continue;
            }
            $line->actual_cost_rate = EstimateMoney::of($lineData['actual_cost_rate'] ?? 0);
            $line->recalculateAmounts();
            $line->save();
        }

        $record->expenses()->delete();
        foreach (array_values($data['expenses'] ?? []) as $index => $expense) {
            $record->expenses()->create([
                'type' => $expense['type'],
                'description' => $expense['description'],
                'amount' => EstimateMoney::of($expense['amount'] ?? 0),
                'sort_order' => $index,
            ]);
        }

        $record->load(['lines', 'expenses']);
        EstimateAuditor::log($record, 'costing_updated', Auth::user(), $record->profitSummary());

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back to estimate')
                ->url(EstimateResource::getUrl('edit', ['record' => $this->getRecord()])),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function profitSummary(): array
    {
        /** @var Estimate $record */
        $record = $this->getRecord()->load(['lines', 'expenses']);

        return $record->profitSummary();
    }
}

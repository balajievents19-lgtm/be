<?php

namespace App\Support\Estimates;

use App\Enums\EstimateRateBasis;
use App\Enums\EstimateStatus;
use App\Models\Estimate;
use App\Models\EstimateLine;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class EstimatePersister
{
    public function __construct(
        private readonly EstimateNumberGenerator $numbers,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  list<array<string, mixed>>  $lines
     */
    public function create(array $data, array $lines, ?User $user = null): Estimate
    {
        return DB::transaction(function () use ($data, $lines, $user): Estimate {
            $payload = $this->sanitizeHeader($data);
            $payload['number'] = $this->numbers->next();
            $payload['status'] = EstimateStatus::tryFrom((string) ($payload['status'] ?? '')) ?? EstimateStatus::Draft;
            $payload['created_by'] = $user?->id;
            $payload['updated_by'] = $user?->id;

            $estimate = new Estimate($payload);
            EstimateCompanySnapshot::apply($estimate, true);
            $estimate->save();

            $this->syncLines($estimate, $lines);
            $estimate->refreshTotalsFromLines();
            $estimate->save();

            EstimateAuditor::log($estimate, 'created', $user, [
                'number' => $estimate->number,
                'grand_total' => (string) $estimate->grand_total,
            ]);

            return $estimate->fresh(['lines', 'expenses']) ?? $estimate;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<array<string, mixed>>  $lines
     */
    public function update(Estimate $estimate, array $data, array $lines, ?User $user = null): Estimate
    {
        return DB::transaction(function () use ($estimate, $data, $lines, $user): Estimate {
            unset($data['number']);
            $before = [
                'grand_total' => (string) $estimate->grand_total,
                'status' => $estimate->status?->value,
            ];

            $payload = $this->sanitizeHeader($data);
            $estimate->fill($payload);
            $estimate->updated_by = $user?->id;
            if (! filled($estimate->issued_company_name)) {
                EstimateCompanySnapshot::apply($estimate);
            }
            $estimate->save();

            $this->syncLines($estimate, $lines);
            $estimate->load('lines');
            $estimate->refreshTotalsFromLines();
            $estimate->save();

            EstimateAuditor::log($estimate->fresh() ?? $estimate, 'updated', $user, [
                'before' => $before,
                'after' => [
                    'grand_total' => (string) $estimate->grand_total,
                    'status' => $estimate->status?->value,
                ],
            ]);

            return $estimate->fresh(['lines', 'expenses']) ?? $estimate;
        });
    }

    public function duplicate(Estimate $source, ?User $user = null): Estimate
    {
        $source->load(['lines', 'expenses']);

        $header = $source->only([
            'customer_id',
            'customer_name',
            'customer_mobile',
            'event_type_id',
            'event_name',
            'event_dates',
            'venue',
            'city',
            'payment_terms',
            'payment_schedule',
            'notes',
            'special_notes',
            'discount_amount',
            'additional_charges',
            'tax_rate',
            'tax_label',
            'advance_amount',
            'currency',
        ]);
        $header['status'] = EstimateStatus::Draft->value;
        $header['estimate_date'] = now()->toDateString();
        $header['valid_until'] = Estimate::defaultValidUntil()->toDateString();

        $lines = $source->lines->map(fn (EstimateLine $line): array => $line->only([
            'catalogue_item_id',
            'name',
            'description',
            'qty',
            'rate',
            'days',
            'rate_basis',
            'actual_cost_rate',
        ]))->all();

        $copy = $this->create($header, $lines, $user);

        foreach ($source->expenses as $index => $expense) {
            $copy->expenses()->create([
                'type' => $expense->type,
                'description' => $expense->description,
                'amount' => $expense->amount,
                'sort_order' => $index,
            ]);
        }

        EstimateAuditor::log($copy, 'duplicated', $user, [
            'from' => $source->number,
        ]);

        return $copy->fresh(['lines', 'expenses']) ?? $copy;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function sanitizeHeader(array $data): array
    {
        unset($data['number'], $data['subtotal'], $data['grand_total'], $data['tax_amount'], $data['balance_due']);

        $estimateDate = isset($data['estimate_date'])
            ? Carbon::parse($data['estimate_date'])->startOfDay()
            : now()->startOfDay();
        $data['estimate_date'] = $estimateDate->toDateString();
        $data['valid_until'] = Estimate::defaultValidUntil($estimateDate)->toDateString();
        $data['discount_amount'] = EstimateMoney::of($data['discount_amount'] ?? 0);
        $data['additional_charges'] = EstimateMoney::of($data['additional_charges'] ?? 0);
        $data['tax_rate'] = EstimateMoney::of($data['tax_rate'] ?? 0);
        $data['advance_amount'] = EstimateMoney::of($data['advance_amount'] ?? 0);
        $data['currency'] = $data['currency'] ?? 'INR';

        return $data;
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    private function syncLines(Estimate $estimate, array $lines): void
    {
        if ($lines === []) {
            throw ValidationException::withMessages([
                'lines' => 'Add at least one estimate item.',
            ]);
        }

        $estimate->lines()->delete();

        foreach (array_values($lines) as $index => $line) {
            $basis = EstimateRateBasis::from((string) ($line['rate_basis'] ?? EstimateRateBasis::OneTime->value));
            $model = new EstimateLine([
                'catalogue_item_id' => $line['catalogue_item_id'] ?? null,
                'sort_order' => $index,
                'name' => trim((string) ($line['name'] ?? '')),
                'description' => $line['description'] ?? null,
                'qty' => EstimateMoney::of($line['qty'] ?? 0),
                'rate' => EstimateMoney::of($line['rate'] ?? 0),
                'days' => $basis === EstimateRateBasis::OneTime ? null : EstimateMoney::of($line['days'] ?? 0),
                'rate_basis' => $basis,
                'actual_cost_rate' => EstimateMoney::of($line['actual_cost_rate'] ?? 0),
            ]);

            if ($model->name === '') {
                throw ValidationException::withMessages([
                    "lines.{$index}.name" => 'Item / Description is required.',
                ]);
            }

            $model->recalculateAmounts();
            $estimate->lines()->save($model);
        }

        $estimate->unsetRelation('lines');
        $estimate->load('lines');
    }
}

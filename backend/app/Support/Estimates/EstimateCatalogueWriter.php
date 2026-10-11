<?php

namespace App\Support\Estimates;

use App\Enums\EstimateRateBasis;
use App\Models\EstimateCatalogueItem;
use Illuminate\Validation\ValidationException;

final class EstimateCatalogueWriter
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function save(array $data, ?EstimateCatalogueItem $item = null): EstimateCatalogueItem
    {
        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '') {
            throw ValidationException::withMessages([
                'name' => 'Item name is required.',
            ]);
        }

        $normalized = EstimateCatalogueItem::normalizeName($name);
        $duplicate = EstimateCatalogueItem::query()
            ->where('name_normalized', $normalized)
            ->when($item, fn ($query) => $query->whereKeyNot($item->id))
            ->first();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'name' => 'A catalogue item with this name already exists.',
            ]);
        }

        $payload = [
            'name' => $name,
            'description' => $data['description'] ?? null,
            'default_rate' => EstimateMoney::of($data['default_rate'] ?? 0),
            'rate_basis' => EstimateRateBasis::from((string) ($data['rate_basis'] ?? EstimateRateBasis::OneTime->value)),
            'actual_cost_rate' => EstimateMoney::of($data['actual_cost_rate'] ?? 0),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ];

        if ($item) {
            $item->fill($payload);
            $item->save();

            return $item;
        }

        return EstimateCatalogueItem::query()->create($payload);
    }
}

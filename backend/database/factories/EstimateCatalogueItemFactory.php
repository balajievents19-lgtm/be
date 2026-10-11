<?php

namespace Database\Factories;

use App\Enums\EstimateRateBasis;
use App\Models\EstimateCatalogueItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EstimateCatalogueItem>
 */
class EstimateCatalogueItemFactory extends Factory
{
    protected $model = EstimateCatalogueItem::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => $name,
            'name_normalized' => EstimateCatalogueItem::normalizeName($name),
            'description' => fake()->sentence(),
            'default_rate' => '15000.00',
            'rate_basis' => EstimateRateBasis::PerDay,
            'actual_cost_rate' => '8000.00',
            'is_active' => true,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Enums\EstimateStatus;
use App\Models\Estimate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Estimate>
 */
class EstimateFactory extends Factory
{
    protected $model = Estimate::class;

    public function definition(): array
    {
        $date = now()->startOfDay();

        return [
            'number' => 'BRE-'.now()->format('Y').'-'.str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'status' => EstimateStatus::Draft,
            'estimate_date' => $date->toDateString(),
            'valid_until' => Estimate::defaultValidUntil($date)->toDateString(),
            'customer_name' => fake()->name(),
            'customer_mobile' => '9462577065',
            'event_name' => 'Wedding',
            'currency' => 'INR',
            'discount_amount' => '0.00',
            'additional_charges' => '0.00',
            'tax_rate' => '0.00',
            'tax_amount' => '0.00',
            'subtotal' => '0.00',
            'grand_total' => '0.00',
            'advance_amount' => '0.00',
            'balance_due' => '0.00',
        ];
    }
}

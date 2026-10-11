<?php

namespace App\Models;

use App\Enums\EstimateRateBasis;
use App\Support\Estimates\EstimateCalculator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstimateLine extends Model
{
    protected $fillable = [
        'estimate_id',
        'catalogue_item_id',
        'sort_order',
        'name',
        'description',
        'qty',
        'rate',
        'days',
        'rate_basis',
        'amount',
        'actual_cost_rate',
        'actual_cost',
    ];

    protected function casts(): array
    {
        return [
            'rate_basis' => EstimateRateBasis::class,
            'qty' => 'decimal:2',
            'rate' => 'decimal:2',
            'days' => 'decimal:2',
            'amount' => 'decimal:2',
            'actual_cost_rate' => 'decimal:2',
            'actual_cost' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Recalculate customer amount and actual cost from qty/rate/days/basis.
     * Never multiplies an already-stored amount by days.
     */
    public function recalculateAmounts(): void
    {
        $this->amount = EstimateCalculator::lineAmount(
            (string) $this->qty,
            (string) $this->rate,
            $this->days === null ? null : (string) $this->days,
            $this->rate_basis,
        );
        $this->actual_cost = EstimateCalculator::lineAmount(
            (string) $this->qty,
            (string) ($this->actual_cost_rate ?? '0'),
            $this->days === null ? null : (string) $this->days,
            $this->rate_basis,
        );
    }

    public function estimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class);
    }

    public function catalogueItem(): BelongsTo
    {
        return $this->belongsTo(EstimateCatalogueItem::class, 'catalogue_item_id');
    }
}

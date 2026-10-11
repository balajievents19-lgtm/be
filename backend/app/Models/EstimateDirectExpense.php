<?php

namespace App\Models;

use App\Enums\EstimateExpenseType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstimateDirectExpense extends Model
{
    protected $fillable = [
        'estimate_id',
        'type',
        'description',
        'amount',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type' => EstimateExpenseType::class,
            'amount' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function estimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class);
    }
}

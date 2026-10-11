<?php

namespace App\Models;

use App\Enums\EstimateStatus;
use App\Support\Estimates\EstimateCalculator;
use App\Support\Estimates\EstimateMoney;
use Database\Factories\EstimateFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Estimate extends Model
{
    /** @use HasFactory<EstimateFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'number',
        'status',
        'estimate_date',
        'valid_until',
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
        'tax_amount',
        'subtotal',
        'grand_total',
        'advance_amount',
        'balance_due',
        'currency',
        'issued_company_name',
        'issued_company_tagline',
        'issued_phone',
        'issued_email',
        'issued_address',
        'issued_website',
        'issued_logo_path',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => EstimateStatus::class,
            'estimate_date' => 'date',
            'valid_until' => 'date',
            'discount_amount' => 'decimal:2',
            'additional_charges' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'advance_amount' => 'decimal:2',
            'balance_due' => 'decimal:2',
        ];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(EstimateLine::class)->orderBy('sort_order')->orderBy('id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(EstimateDirectExpense::class)->orderBy('sort_order')->orderBy('id');
    }

    public function audits(): HasMany
    {
        return $this->hasMany(EstimateAudit::class)->latest();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(EventType::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function defaultValidUntil(?Carbon $estimateDate = null): Carbon
    {
        return ($estimateDate ?? now())->copy()->startOfDay()->addDays(5);
    }

    public function refreshTotalsFromLines(): void
    {
        $lineAmounts = $this->lines->map(function (EstimateLine $line): string {
            $line->recalculateAmounts();

            return (string) $line->amount;
        })->all();

        $totals = EstimateCalculator::documentTotals(
            $lineAmounts,
            (string) $this->discount_amount,
            (string) $this->additional_charges,
            (string) $this->tax_rate,
            (string) $this->advance_amount,
        );

        $this->subtotal = $totals['subtotal'];
        $this->discount_amount = $totals['discount_amount'];
        $this->additional_charges = $totals['additional_charges'];
        $this->tax_amount = $totals['tax_amount'];
        $this->grand_total = $totals['grand_total'];
        $this->advance_amount = $totals['advance_amount'];
        $this->balance_due = $totals['balance_due'];
    }

    /**
     * @return array{
     *     client_amount: string,
     *     client_net_amount: string,
     *     total_actual_item_cost: string,
     *     additional_direct_expenses: string,
     *     total_actual_cost: string,
     *     profit: string,
     *     profit_margin_percent: ?string
     * }
     */
    public function profitSummary(): array
    {
        $itemCost = '0.00';
        foreach ($this->lines as $line) {
            $line->recalculateAmounts();
            $itemCost = EstimateMoney::add($itemCost, (string) $line->actual_cost);
        }

        $direct = '0.00';
        foreach ($this->expenses as $expense) {
            $direct = EstimateMoney::add($direct, (string) $expense->amount);
        }

        return EstimateCalculator::profitSummary(
            (string) $this->grand_total,
            (string) $this->tax_amount,
            $itemCost,
            $direct,
        );
    }

    public function displayStatus(): EstimateStatus
    {
        $status = $this->status ?? EstimateStatus::Draft;
        if ($status->isOpen() && $this->valid_until !== null && $this->valid_until->lt(now()->startOfDay())) {
            return EstimateStatus::Expired;
        }

        return $status;
    }

    public function scopeOpenPastValidity(Builder $query): Builder
    {
        return $query->whereIn('status', [EstimateStatus::Draft->value, EstimateStatus::Sent->value])
            ->whereDate('valid_until', '<', now()->toDateString());
    }
}

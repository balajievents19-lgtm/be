<?php

namespace App\Models;

use App\Enums\EstimateRateBasis;
use Database\Factories\EstimateCatalogueItemFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class EstimateCatalogueItem extends Model
{
    /** @use HasFactory<EstimateCatalogueItemFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'name',
        'name_normalized',
        'description',
        'default_rate',
        'rate_basis',
        'actual_cost_rate',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rate_basis' => EstimateRateBasis::class,
            'default_rate' => 'decimal:2',
            'actual_cost_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public static function normalizeName(string $name): string
    {
        $collapsed = preg_replace('/\s+/u', ' ', trim($name)) ?? trim($name);

        return Str::lower($collapsed);
    }

    protected static function booted(): void
    {
        static::saving(function (EstimateCatalogueItem $item): void {
            $item->name = trim((string) $item->name);
            $item->name_normalized = self::normalizeName($item->name);
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}

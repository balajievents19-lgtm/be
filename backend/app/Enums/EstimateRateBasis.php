<?php

namespace App\Enums;

enum EstimateRateBasis: string
{
    case PerDay = 'per_day';
    case OneTime = 'one_time';

    public function label(): string
    {
        return match ($this) {
            self::PerDay => 'Per Day',
            self::OneTime => 'One-Time',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $basis) => [$basis->value => $basis->label()])
            ->all();
    }
}

<?php

namespace App\Enums;

enum EstimateExpenseType: string
{
    case Labour = 'labour';
    case Transport = 'transport';
    case Vendor = 'vendor';
    case Rental = 'rental';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Labour => 'Labour',
            self::Transport => 'Transport',
            self::Vendor => 'Vendor payments',
            self::Rental => 'Rentals/purchases',
            self::Other => 'Other',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type) => [$type->value => $type->label()])
            ->all();
    }
}

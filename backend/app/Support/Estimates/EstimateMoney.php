<?php

namespace App\Support\Estimates;

use InvalidArgumentException;

/**
 * Fixed-scale decimal arithmetic for estimate money (2 decimal places).
 * Avoids binary floating-point rounding on line totals and profit.
 */
final class EstimateMoney
{
    public const SCALE = 2;

    public static function of(string|int|float|null $value): string
    {
        if ($value === null || $value === '') {
            return '0.00';
        }

        if (is_int($value)) {
            return number_format($value, self::SCALE, '.', '');
        }

        $raw = is_float($value)
            ? number_format($value, 6, '.', '')
            : trim((string) $value);

        $raw = str_replace([',', ' '], '', $raw);

        if ($raw === '' || ! is_numeric($raw)) {
            throw new InvalidArgumentException('Invalid money value.');
        }

        if (function_exists('bcadd')) {
            return bcadd($raw, '0', self::SCALE);
        }

        return number_format((float) $raw, self::SCALE, '.', '');
    }

    public static function add(string $left, string $right): string
    {
        $left = self::of($left);
        $right = self::of($right);

        if (function_exists('bcadd')) {
            return bcadd($left, $right, self::SCALE);
        }

        return self::of(((float) $left) + ((float) $right));
    }

    public static function sub(string $left, string $right): string
    {
        $left = self::of($left);
        $right = self::of($right);

        if (function_exists('bcsub')) {
            return bcsub($left, $right, self::SCALE);
        }

        return self::of(((float) $left) - ((float) $right));
    }

    public static function mul(string $left, string $right): string
    {
        $left = self::of($left);
        $right = self::of($right);

        if (function_exists('bcmul')) {
            return bcmul($left, $right, self::SCALE);
        }

        return self::of(((float) $left) * ((float) $right));
    }

    /**
     * Division at a working scale of 6, then rounded to money scale.
     */
    public static function div(string $left, string $right): string
    {
        $left = self::of($left);
        $right = self::of($right);

        if (self::compare($right, '0') === 0) {
            throw new InvalidArgumentException('Division by zero.');
        }

        if (function_exists('bcdiv') && function_exists('bcadd')) {
            return bcadd(bcdiv($left, $right, 6), '0', self::SCALE);
        }

        return self::of(((float) $left) / ((float) $right));
    }

    public static function compare(string $left, string $right): int
    {
        $left = self::of($left);
        $right = self::of($right);

        if (function_exists('bccomp')) {
            return bccomp($left, $right, self::SCALE);
        }

        return $left <=> $right;
    }
}

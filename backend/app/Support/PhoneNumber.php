<?php

namespace App\Support;

use App\Models\Customer;
use Closure;

final class PhoneNumber
{
    /**
     * Customer/inquiry input must be exactly 10 numeric digits.
     * Rejects +91, spaces, hyphens, letters, and other formatting.
     */
    public static function isExactTenDigits(?string $value): bool
    {
        return is_string($value) && preg_match('/^[0-9]{10}$/', $value) === 1;
    }

    /**
     * Last 10 digits of stored values (legacy 91-prefixed rows included).
     * Not used to accept formatted input on write.
     */
    public static function localTen(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value) ?? '';
        if (strlen($digits) === 10) {
            return $digits;
        }

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return substr($digits, 2);
        }

        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            return substr($digits, 1);
        }

        return null;
    }

    /**
     * @deprecated Use isExactTenDigits() for writes. Kept for login lookup of legacy rows.
     */
    public static function normalize(?string $value): ?string
    {
        return self::localTen($value);
    }

    public static function toE164(?string $value): ?string
    {
        $local = self::localTen($value);

        return $local === null ? null : '+91'.$local;
    }

    public static function mask(?string $value): string
    {
        $local = self::localTen($value) ?? '';
        if (strlen($local) < 4) {
            return '****';
        }

        return str_repeat('*', max(0, strlen($local) - 4)).substr($local, -4);
    }

    public static function uniqueCustomerRule(?int $ignoreId = null): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($ignoreId): void {
            if (! is_string($value) || ! self::isExactTenDigits($value)) {
                return;
            }

            $exists = Customer::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where(function ($query) use ($value): void {
                    $query->where('phone', $value)
                        ->orWhere('phone', '91'.$value)
                        ->orWhere('phone', '0'.$value);
                })
                ->exists();

            if ($exists) {
                $fail('This mobile number is already registered.');
            }
        };
    }
}

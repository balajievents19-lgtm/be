<?php

namespace App\Support\Estimates;

use App\Enums\EstimateRateBasis;
use InvalidArgumentException;

/**
 * Single source of truth for estimate line and profit math.
 *
 * Profit rule (net-revenue basis):
 * - Client amount for profit = grand total − tax amount.
 *   Tax collected on behalf of authorities is not treated as business profit.
 * - Total actual cost = sum of actual item costs + additional direct event expenses.
 * - Profit = client net amount − total actual cost.
 * - Margin % = profit / client net amount × 100, or null when client net is 0.
 *
 * Line amounts are never multiplied twice: callers persist the computed amount
 * and later reads use stored qty/rate/days/basis rather than stored amount × days.
 */
final class EstimateCalculator
{
    public static function lineAmount(
        string|int|float $qty,
        string|int|float $rate,
        string|int|float|null $days,
        EstimateRateBasis|string $basis,
    ): string {
        $basis = $basis instanceof EstimateRateBasis
            ? $basis
            : EstimateRateBasis::from((string) $basis);

        $qty = EstimateMoney::of($qty);
        $rate = EstimateMoney::of($rate);

        if (EstimateMoney::compare($qty, '0') < 0 || EstimateMoney::compare($rate, '0') < 0) {
            throw new InvalidArgumentException('Quantity and rate cannot be negative.');
        }

        if ($basis === EstimateRateBasis::OneTime) {
            return EstimateMoney::mul($qty, $rate);
        }

        $dayCount = EstimateMoney::of($days ?? '0');
        if (EstimateMoney::compare($dayCount, '0') <= 0) {
            throw new InvalidArgumentException('Days are required for Per Day items.');
        }

        return EstimateMoney::mul(EstimateMoney::mul($qty, $rate), $dayCount);
    }

    /**
     * @param  list<string>  $lineAmounts
     * @return array{
     *     subtotal: string,
     *     discount_amount: string,
     *     additional_charges: string,
     *     taxable_amount: string,
     *     tax_rate: string,
     *     tax_amount: string,
     *     grand_total: string,
     *     advance_amount: string,
     *     balance_due: string
     * }
     */
    public static function documentTotals(
        array $lineAmounts,
        string|int|float|null $discount = 0,
        string|int|float|null $additionalCharges = 0,
        string|int|float|null $taxRate = 0,
        string|int|float|null $advance = 0,
    ): array {
        $subtotal = '0.00';
        foreach ($lineAmounts as $amount) {
            $subtotal = EstimateMoney::add($subtotal, EstimateMoney::of($amount));
        }

        $discountAmount = EstimateMoney::of($discount);
        $charges = EstimateMoney::of($additionalCharges);
        $rate = EstimateMoney::of($taxRate);
        $advanceAmount = EstimateMoney::of($advance);

        if (EstimateMoney::compare($discountAmount, '0') < 0
            || EstimateMoney::compare($charges, '0') < 0
            || EstimateMoney::compare($rate, '0') < 0
            || EstimateMoney::compare($advanceAmount, '0') < 0) {
            throw new InvalidArgumentException('Discount, charges, tax rate, and advance cannot be negative.');
        }

        $taxable = EstimateMoney::sub(EstimateMoney::add($subtotal, $charges), $discountAmount);
        if (EstimateMoney::compare($taxable, '0') < 0) {
            throw new InvalidArgumentException('Discount cannot exceed subtotal plus additional charges.');
        }

        $taxAmount = EstimateMoney::mul($taxable, EstimateMoney::div($rate, '100'));
        $grandTotal = EstimateMoney::add($taxable, $taxAmount);

        if (EstimateMoney::compare($advanceAmount, $grandTotal) > 0) {
            throw new InvalidArgumentException('Advance cannot exceed the grand total.');
        }

        return [
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'additional_charges' => $charges,
            'taxable_amount' => $taxable,
            'tax_rate' => $rate,
            'tax_amount' => $taxAmount,
            'grand_total' => $grandTotal,
            'advance_amount' => $advanceAmount,
            'balance_due' => EstimateMoney::sub($grandTotal, $advanceAmount),
        ];
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
    public static function profitSummary(
        string $grandTotal,
        string $taxAmount,
        string $totalActualItemCost,
        string $additionalDirectExpenses,
    ): array {
        $clientAmount = EstimateMoney::of($grandTotal);
        $tax = EstimateMoney::of($taxAmount);
        $itemCost = EstimateMoney::of($totalActualItemCost);
        $direct = EstimateMoney::of($additionalDirectExpenses);
        $clientNet = EstimateMoney::sub($clientAmount, $tax);
        $totalCost = EstimateMoney::add($itemCost, $direct);
        $profit = EstimateMoney::sub($clientNet, $totalCost);

        $margin = null;
        if (EstimateMoney::compare($clientNet, '0') !== 0) {
            $margin = EstimateMoney::mul(EstimateMoney::div($profit, $clientNet), '100');
        }

        return [
            'client_amount' => $clientAmount,
            'client_net_amount' => $clientNet,
            'total_actual_item_cost' => $itemCost,
            'additional_direct_expenses' => $direct,
            'total_actual_cost' => $totalCost,
            'profit' => $profit,
            'profit_margin_percent' => $margin,
        ];
    }
}

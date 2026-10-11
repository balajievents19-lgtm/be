<?php

namespace Tests\Feature\Estimates;

use App\Enums\EstimateRateBasis;
use App\Support\Estimates\EstimateCalculator;
use InvalidArgumentException;
use Tests\TestCase;

class EstimateCalculatorTest extends TestCase
{
    public function test_per_day_amount_is_qty_times_rate_times_days(): void
    {
        $this->assertSame(
            '30000.00',
            EstimateCalculator::lineAmount(1, 15000, 2, EstimateRateBasis::PerDay)
        );
    }

    public function test_one_time_amount_ignores_days(): void
    {
        $this->assertSame(
            '12000.00',
            EstimateCalculator::lineAmount(1, 12000, 9, EstimateRateBasis::OneTime)
        );
    }

    public function test_profit_margin_is_null_when_client_net_is_zero(): void
    {
        $summary = EstimateCalculator::profitSummary('0', '0', '500', '0');

        $this->assertSame('0.00', $summary['client_net_amount']);
        $this->assertSame('-500.00', $summary['profit']);
        $this->assertNull($summary['profit_margin_percent']);
    }

    public function test_profit_uses_net_revenue_excluding_tax(): void
    {
        $summary = EstimateCalculator::profitSummary('11800', '1800', '5000', '1000');

        $this->assertSame('10000.00', $summary['client_net_amount']);
        $this->assertSame('6000.00', $summary['total_actual_cost']);
        $this->assertSame('4000.00', $summary['profit']);
        $this->assertSame('40.00', $summary['profit_margin_percent']);
    }

    public function test_negative_qty_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        EstimateCalculator::lineAmount(-1, 100, 1, EstimateRateBasis::OneTime);
    }
}

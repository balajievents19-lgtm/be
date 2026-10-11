<?php

namespace App\Console\Commands;

use App\Enums\EstimateStatus;
use App\Models\Estimate;
use App\Support\Estimates\EstimateAuditor;
use Illuminate\Console\Command;

class ExpireEstimatesCommand extends Command
{
    protected $signature = 'estimates:expire';

    protected $description = 'Mark open estimates as expired after their validity date without changing financial values.';

    public function handle(): int
    {
        $count = 0;

        Estimate::query()
            ->openPastValidity()
            ->orderBy('id')
            ->each(function (Estimate $estimate) use (&$count): void {
                $totals = $estimate->only([
                    'subtotal',
                    'discount_amount',
                    'additional_charges',
                    'tax_amount',
                    'grand_total',
                    'advance_amount',
                    'balance_due',
                ]);

                $estimate->status = EstimateStatus::Expired;
                $estimate->save();

                EstimateAuditor::log($estimate, 'expired', null, [
                    'valid_until' => $estimate->valid_until?->toDateString(),
                    'financials_unchanged' => $totals,
                ]);
                $count++;
            });

        $this->info("Expired {$count} estimate(s).");

        return self::SUCCESS;
    }
}

<?php

namespace App\Support\Estimates;

use App\Models\EstimateNumberSequence;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final class EstimateNumberGenerator
{
    public function next(?int $year = null): string
    {
        $year ??= (int) now()->format('Y');
        $attempts = 0;

        while ($attempts < 5) {
            $attempts++;

            try {
                return DB::transaction(function () use ($year): string {
                    $sequence = EstimateNumberSequence::query()
                        ->where('year', $year)
                        ->lockForUpdate()
                        ->first();

                    if ($sequence === null) {
                        EstimateNumberSequence::query()->create([
                            'year' => $year,
                            'last_number' => 0,
                        ]);
                        $sequence = EstimateNumberSequence::query()
                            ->where('year', $year)
                            ->lockForUpdate()
                            ->first();
                    }

                    if ($sequence === null) {
                        throw new RuntimeException('Unable to lock estimate number sequence.');
                    }

                    $sequence->last_number++;
                    $sequence->save();

                    return sprintf('BRE-%d-%04d', $year, $sequence->last_number);
                });
            } catch (Throwable $exception) {
                if ($attempts >= 5) {
                    throw $exception;
                }

                usleep(25_000);
            }
        }

        throw new RuntimeException('Unable to allocate an estimate number.');
    }
}

<?php

namespace App\Support\Estimates;

use App\Models\Estimate;
use App\Models\EstimateAudit;
use App\Models\User;

final class EstimateAuditor
{
    /**
     * @param  array<string, mixed>|null  $changes
     */
    public static function log(Estimate $estimate, string $action, ?User $user = null, ?array $changes = null): void
    {
        EstimateAudit::query()->create([
            'estimate_id' => $estimate->id,
            'user_id' => $user?->id,
            'action' => $action,
            'changes' => $changes,
        ]);
    }
}

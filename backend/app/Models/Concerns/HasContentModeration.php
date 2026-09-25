<?php

namespace App\Models\Concerns;

use App\Enums\ContentModerationStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasContentModeration
{
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isOwnedBy(?User $user): bool
    {
        return $user !== null && (int) $this->created_by === (int) $user->id;
    }

    public function isPubliclyVisible(): bool
    {
        $status = (string) ($this->moderation_status ?: ContentModerationStatus::Published->value);

        return (bool) $this->status
            && in_array($status, ContentModerationStatus::publicValues(), true);
    }

    public function scopePubliclyModerated(Builder $query): Builder
    {
        $table = $query->getModel()->getTable();

        return $query->where(function (Builder $builder) use ($table): void {
            $builder
                ->whereNull($table.'.moderation_status')
                ->orWhereIn($table.'.moderation_status', ContentModerationStatus::publicValues());
        });
    }

    public function scopeNeedsReview(Builder $query): Builder
    {
        $table = $query->getModel()->getTable();

        return $query->whereIn($table.'.moderation_status', ContentModerationStatus::reviewQueueValues());
    }
}

<?php

namespace App\Models;

use App\Enums\ContactInquiryPriority;
use App\Enums\ContactInquiryStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactInquiry extends Model
{
    protected $fillable = [
        'name',
        'mobile',
        'email',
        'company',
        'subject',
        'message',
        'service_interested',
        'event_date',
        'event_location',
        'budget',
        'source',
        'status',
        'priority',
        'assigned_to',
        'follow_up_at',
        'admin_notes',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'follow_up_at' => 'datetime',
            'status' => ContactInquiryStatus::class,
            'priority' => ContactInquiryPriority::class,
        ];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scopeStatus(Builder $query, ContactInquiryStatus|string $status): Builder
    {
        $value = $status instanceof ContactInquiryStatus ? $status->value : $status;

        return $query->where('status', $value);
    }

    public function scopePriority(Builder $query, ContactInquiryPriority|string $priority): Builder
    {
        $value = $priority instanceof ContactInquiryPriority ? $priority->value : $priority;

        return $query->where('priority', $value);
    }
}

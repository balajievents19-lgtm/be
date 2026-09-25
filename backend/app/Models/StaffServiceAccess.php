<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffServiceAccess extends Model
{
    protected $fillable = [
        'user_id',
        'service_id',
        'can_access',
        'can_create',
        'can_edit_own',
    ];

    protected function casts(): array
    {
        return [
            'can_access' => 'boolean',
            'can_create' => 'boolean',
            'can_edit_own' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}

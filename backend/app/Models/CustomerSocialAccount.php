<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerSocialAccount extends Model
{
    protected $fillable = [
        'customer_id',
        'provider',
        'provider_user_id',
        'provider_email',
        'provider_name',
        'avatar',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}

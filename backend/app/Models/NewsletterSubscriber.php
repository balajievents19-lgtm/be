<?php

namespace App\Models;

use App\Enums\NewsletterSubscriberStatus;
use Illuminate\Database\Eloquent\Model;

class NewsletterSubscriber extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'status',
        'ip_address',
        'subscribed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => NewsletterSubscriberStatus::class,
            'subscribed_at' => 'datetime',
        ];
    }
}

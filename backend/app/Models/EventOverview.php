<?php

namespace App\Models;

use App\Models\Concerns\HasPublicStorageUrl;
use App\Models\Concerns\HasPublishableScopes;
use App\Models\Concerns\Publication\HasPublicationWindow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventOverview extends Model
{
    use HasPublicationWindow;
    use HasPublicStorageUrl;
    use HasPublishableScopes;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'caption',
        'description',
        'image',
        'link_url',
        'sort_order',
        'featured',
        'homepage_featured',
        'status',
        'publish_at',
        'unpublish_at',
    ];

    protected function casts(): array
    {
        return [
            'featured' => 'boolean',
            'homepage_featured' => 'boolean',
            'status' => 'boolean',
            'sort_order' => 'integer',
            'publish_at' => 'datetime',
            'unpublish_at' => 'datetime',
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffGalleryCategoryAccess extends Model
{
    protected $fillable = [
        'user_id',
        'gallery_category_id',
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(GalleryCategory::class, 'gallery_category_id');
    }
}

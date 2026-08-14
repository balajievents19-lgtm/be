<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;

trait HasPublicStorageUrl
{
    public function imageUrl(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }
}

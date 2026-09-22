<?php

namespace App\Models\Concerns;

use App\Support\Media\PublicStorageUrl;

trait HasPublicStorageUrl
{
    /**
     * Public CMS image URL for the website.
     *
     * Same-site storage files are returned as origin-relative /storage/... paths so
     * they work on IP previews and on the real domain. External URLs are unchanged.
     */
    public function imageUrl(?string $path): ?string
    {
        return PublicStorageUrl::make($path);
    }
}

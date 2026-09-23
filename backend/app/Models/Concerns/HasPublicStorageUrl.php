<?php

namespace App\Models\Concerns;

use App\Support\Media\PublicStorageUrl;

trait HasPublicStorageUrl
{
    /**
     * Public CMS image URL for the website.
     *
     * Same-site CMS files are returned as origin-relative protected display URLs.
     * External URLs are unchanged. Original /storage paths are not exposed.
     */
    public function imageUrl(?string $path): ?string
    {
        return PublicStorageUrl::make($path);
    }
}

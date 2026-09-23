<?php

namespace App\Filament\Tables\Columns;

use App\Support\Media\AdminPreviewMedia;
use App\Support\Media\ProtectedMedia;
use Filament\Tables\Columns\ImageColumn;
use League\Flysystem\UnableToCheckFileExistence;

class AdminPreviewImageColumn extends ImageColumn
{
    public function getImageUrl(?string $state = null): ?string
    {
        if ($state === null || $state === '') {
            return parent::getImageUrl($state);
        }

        if ((filter_var($state, FILTER_VALIDATE_URL) !== false) || str($state)->startsWith('data:')) {
            return $state;
        }

        if (
            str_starts_with($state, AdminPreviewMedia::URL_PREFIX)
            || str_starts_with($state, ProtectedMedia::URL_PREFIX)
        ) {
            return $state;
        }

        $relative = ProtectedMedia::normalizeRelative($state);
        if ($relative === null) {
            return parent::getImageUrl($state);
        }

        if ($this->shouldCheckFileExistence()) {
            try {
                if (! $this->getDisk()->exists($state)) {
                    return null;
                }
            } catch (UnableToCheckFileExistence) {
                return null;
            }
        }

        return AdminPreviewMedia::url($relative) ?? parent::getImageUrl($state);
    }
}

<?php

namespace App\Support\Media;

use Filament\Forms\Components\BaseFileUpload;

/**
 * Opaque admin-only CMS preview URLs. Paths never appear in the URL.
 * Public visitors still use protected-media; this route is not a public bypass.
 */
final class AdminPreviewMedia
{
    public const URL_PREFIX = '/admin/preview/';

    public static function url(string $relativePath): ?string
    {
        $normalized = ProtectedMedia::normalizeRelative($relativePath);
        if ($normalized === null) {
            return null;
        }

        return self::URL_PREFIX.ProtectedMedia::token($normalized);
    }

    public static function pathFromToken(string $token): ?string
    {
        return ProtectedMedia::pathFromToken($token);
    }

    /**
     * @param  string|array<string, string>|null  $storedFileNames
     * @return array{name: string, size: int, type: ?string, url: ?string}|null
     */
    public static function uploadedFilePayload(BaseFileUpload $component, string $file, string|array|null $storedFileNames): ?array
    {
        $info = $component->getUploadedFile($file, $storedFileNames);
        if ($info === null) {
            return null;
        }

        if ($component->getDiskName() !== 'public') {
            return $info;
        }

        $preview = self::url($file);
        if ($preview !== null) {
            $info['url'] = $preview;
        }

        return $info;
    }
}

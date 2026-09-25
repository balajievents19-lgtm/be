<?php

namespace App\Support\Media;

use App\Support\Brand;
use GdImage;
use RuntimeException;

/**
 * Resize + watermark for public display and customer downloads.
 * Does not alter stored originals.
 */
final class DisplayImageFactory
{
    public const MAX_WIDTH = 1600;

    public const MODE_DISPLAY = 'display';

    public const MODE_DOWNLOAD = 'download';

    public const FORMAT_AVIF = 'avif';

    public const FORMAT_WEBP = 'webp';

    public const FORMAT_JPEG = 'jpeg';

    public const FORMAT_PNG = 'png';

    /**
     * Pick a display encode format from Accept. Logos/GIF/SVG/ICO stay on their source type.
     */
    public function negotiateDisplayFormat(?string $accept, string $relativePath, ?string $sourceMime = null): string
    {
        if ($this->mustPreserveSourceFormat($relativePath, $sourceMime)) {
            return $this->preservedFormat($relativePath, $sourceMime);
        }

        $accept = strtolower((string) $accept);
        if (str_contains($accept, 'image/avif') && function_exists('imageavif')) {
            return self::FORMAT_AVIF;
        }
        if (str_contains($accept, 'image/webp') && function_exists('imagewebp')) {
            return self::FORMAT_WEBP;
        }

        return self::FORMAT_JPEG;
    }

    public function downloadFormat(string $relativePath, ?string $sourceMime = null): string
    {
        if ($this->mustPreserveSourceFormat($relativePath, $sourceMime)) {
            return $this->preservedFormat($relativePath, $sourceMime);
        }

        return self::FORMAT_JPEG;
    }

    /**
     * @return array{contents: string, mime: string}
     */
    public function make(?string $binary, string $relativePath, string $mode = self::MODE_DISPLAY, ?string $format = null): array
    {
        $fallbackMime = $this->guessMime($binary, $relativePath);
        $format ??= $mode === self::MODE_DOWNLOAD
            ? $this->downloadFormat($relativePath, $fallbackMime)
            : self::FORMAT_WEBP;
        $allowOriginalFallback = $mode !== self::MODE_DOWNLOAD;

        if (! is_string($binary) || $binary === '' || ! function_exists('imagecreatefromstring')) {
            return $this->fallbackOrFail($binary, $fallbackMime, $allowOriginalFallback);
        }

        $source = @imagecreatefromstring($binary);
        if (! $source instanceof GdImage) {
            return $this->fallbackOrFail($binary, $fallbackMime, $allowOriginalFallback);
        }

        $width = imagesx($source);
        $height = imagesy($source);
        if ($width < 1 || $height < 1) {
            imagedestroy($source);

            return $this->fallbackOrFail($binary, $fallbackMime, $allowOriginalFallback);
        }

        $canvas = $source;
        if ($mode === self::MODE_DISPLAY && $width > self::MAX_WIDTH) {
            $targetWidth = self::MAX_WIDTH;
            $targetHeight = max(1, (int) round($height * ($targetWidth / $width)));
            $resized = imagecreatetruecolor($targetWidth, $targetHeight);
            if ($resized instanceof GdImage) {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                imagecopyresampled($resized, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
                imagedestroy($source);
                $canvas = $resized;
                $width = $targetWidth;
                $height = $targetHeight;
            }
        }

        imagealphablending($canvas, true);
        imagesavealpha($canvas, true);
        $this->paintTiledWatermark($canvas, $width, $height, Brand::NAME, $mode);

        $encoded = $this->encode($canvas, $format, $mode);
        imagedestroy($canvas);

        if ($encoded['contents'] === '') {
            return $this->fallbackOrFail($binary, $fallbackMime, $allowOriginalFallback);
        }

        return $encoded;
    }

    /**
     * Full-resolution watermarked derivative for customer download. Never returns original bytes.
     *
     * @return array{contents: string, mime: string}
     */
    public function makeForDownload(string $binary, string $relativePath): array
    {
        $rendered = $this->make($binary, $relativePath, self::MODE_DOWNLOAD);

        if ($rendered['contents'] === '' || $rendered['contents'] === $binary) {
            throw new RuntimeException('Unable to create a watermarked download derivative.');
        }

        return $rendered;
    }

    /**
     * 3×3 tiled "Balaji Royal Events" watermark. Original pixels on disk are not written.
     */
    private function paintTiledWatermark(GdImage $image, int $width, int $height, string $label, string $mode): void
    {
        $minSide = min($width, $height);
        $maxScale = $mode === self::MODE_DOWNLOAD ? 11 : 8;
        $scale = max(2, min($maxScale, (int) round($minSide / 150)));
        $charWidth = imagefontwidth(5) * strlen($label);
        $maxTextWidth = max($charWidth, (int) round($width * 0.28));
        if ($charWidth * $scale > $maxTextWidth) {
            $scale = max(1, (int) floor($maxTextWidth / $charWidth));
        }
        $textWidth = $charWidth * $scale;
        $textHeight = imagefontheight(5) * $scale;
        $inkAlpha = $mode === self::MODE_DOWNLOAD ? 42 : 52;
        $shadowAlpha = 72;

        foreach ([0.18, 0.50, 0.82] as $fy) {
            foreach ([0.18, 0.50, 0.82] as $fx) {
                $x = (int) round(($width * $fx) - ($textWidth / 2));
                $y = (int) round(($height * $fy) - ($textHeight / 2));
                $x = max(4, min($width - $textWidth - 4, $x));
                $y = max(4, min($height - $textHeight - 4, $y));
                $this->drawScaledString($image, $label, $x + 2, $y + 2, $scale, 0, 0, 0, $shadowAlpha);
                $this->drawScaledString($image, $label, $x, $y, $scale, 255, 255, 255, $inkAlpha);
            }
        }
    }

    private function drawScaledString(
        GdImage $dest,
        string $text,
        int $destX,
        int $destY,
        int $scale,
        int $r,
        int $g,
        int $b,
        int $alpha
    ): void {
        $font = 5;
        $tw = imagefontwidth($font) * strlen($text);
        $th = imagefontheight($font);
        $tmp = imagecreatetruecolor($tw, $th);
        if (! $tmp instanceof GdImage) {
            return;
        }

        imagealphablending($tmp, false);
        imagesavealpha($tmp, true);
        $transparent = imagecolorallocatealpha($tmp, 0, 0, 0, 127);
        imagefilledrectangle($tmp, 0, 0, $tw, $th, $transparent);
        imagealphablending($tmp, true);
        $color = imagecolorallocatealpha($tmp, $r, $g, $b, $alpha);
        imagestring($tmp, $font, 0, 0, $text, $color);

        $dw = max(1, $tw * $scale);
        $dh = max(1, $th * $scale);
        imagecopyresampled($dest, $tmp, $destX, $destY, 0, 0, $dw, $dh, $tw, $th);
        imagedestroy($tmp);
    }

    /**
     * @return array{contents: string, mime: string}
     */
    private function encode(GdImage $canvas, string $format, string $mode): array
    {
        $chain = match ($format) {
            self::FORMAT_AVIF => [self::FORMAT_AVIF, self::FORMAT_WEBP, self::FORMAT_JPEG],
            self::FORMAT_WEBP => [self::FORMAT_WEBP, self::FORMAT_JPEG],
            self::FORMAT_PNG => [self::FORMAT_PNG],
            default => [self::FORMAT_JPEG],
        };

        foreach ($chain as $candidate) {
            $encoded = $this->encodeAs($canvas, $candidate, $mode);
            if ($encoded['contents'] !== '') {
                return $encoded;
            }
        }

        return [
            'contents' => '',
            'mime' => $this->mimeForFormat($format),
        ];
    }

    /**
     * @return array{contents: string, mime: string}
     */
    private function encodeAs(GdImage $canvas, string $format, string $mode): array
    {
        $displayQuality = match ($format) {
            self::FORMAT_AVIF => 58,
            self::FORMAT_WEBP => 80,
            default => 82,
        };
        $quality = $mode === self::MODE_DOWNLOAD ? 90 : $displayQuality;

        $target = $canvas;
        $scratch = null;
        if ($format === self::FORMAT_JPEG) {
            $scratch = $this->flattenForJpeg($canvas);
            if ($scratch instanceof GdImage) {
                $target = $scratch;
            }
        }

        ob_start();
        $ok = false;
        if ($format === self::FORMAT_AVIF && function_exists('imageavif')) {
            $ok = @imageavif($target, null, $quality);
        } elseif ($format === self::FORMAT_WEBP && function_exists('imagewebp')) {
            $ok = @imagewebp($target, null, $quality);
        } elseif ($format === self::FORMAT_PNG) {
            $ok = imagepng($target, null, 6);
        } else {
            $ok = imagejpeg($target, null, $quality);
            $format = self::FORMAT_JPEG;
        }
        $contents = (string) ob_get_clean();
        if ($scratch instanceof GdImage) {
            imagedestroy($scratch);
        }

        if (! $ok || $contents === '') {
            return ['contents' => '', 'mime' => $this->mimeForFormat($format)];
        }

        return [
            'contents' => $contents,
            'mime' => $this->mimeForFormat($format),
        ];
    }

    private function flattenForJpeg(GdImage $canvas): ?GdImage
    {
        $width = imagesx($canvas);
        $height = imagesy($canvas);
        $flat = imagecreatetruecolor($width, $height);
        if (! $flat instanceof GdImage) {
            return null;
        }

        $white = imagecolorallocate($flat, 255, 255, 255);
        imagefilledrectangle($flat, 0, 0, $width - 1, $height - 1, $white);
        imagealphablending($flat, true);
        imagecopy($flat, $canvas, 0, 0, 0, 0, $width, $height);

        return $flat;
    }

    private function mustPreserveSourceFormat(string $relativePath, ?string $sourceMime): bool
    {
        $mime = strtolower((string) $sourceMime);
        $extension = strtolower((string) pathinfo($relativePath, PATHINFO_EXTENSION));
        if (in_array($extension, ['svg', 'gif', 'ico'], true)) {
            return true;
        }
        if (str_contains($mime, 'svg') || str_contains($mime, 'gif') || str_contains($mime, 'icon')) {
            return true;
        }

        return $this->isBrandAsset($relativePath);
    }

    private function isBrandAsset(string $relativePath): bool
    {
        $path = strtolower(str_replace('\\', '/', $relativePath));
        $base = basename($path);

        return str_contains($base, 'logo')
            || str_contains($base, 'favicon')
            || str_contains($path, '/brand/')
            || str_contains($path, 'settings/brand');
    }

    private function preservedFormat(string $relativePath, ?string $sourceMime): string
    {
        $mime = strtolower((string) $sourceMime);
        $extension = strtolower((string) pathinfo($relativePath, PATHINFO_EXTENSION));
        if (str_contains($mime, 'jpeg') || in_array($extension, ['jpg', 'jpeg'], true)) {
            return self::FORMAT_JPEG;
        }
        if (str_contains($mime, 'webp') || $extension === 'webp') {
            return self::FORMAT_WEBP;
        }

        return self::FORMAT_PNG;
    }

    private function mimeForFormat(string $format): string
    {
        return match ($format) {
            self::FORMAT_AVIF => 'image/avif',
            self::FORMAT_WEBP => 'image/webp',
            self::FORMAT_PNG => 'image/png',
            default => 'image/jpeg',
        };
    }

    /**
     * @return array{contents: string, mime: string}
     */
    private function fallbackOrFail(?string $binary, string $mime, bool $allowOriginalFallback): array
    {
        if (! $allowOriginalFallback) {
            throw new RuntimeException('Unable to create a watermarked download derivative.');
        }

        return [
            'contents' => is_string($binary) ? $binary : '',
            'mime' => $mime,
        ];
    }

    private function guessMime(?string $binary, string $relativePath): string
    {
        $extension = strtolower((string) pathinfo($relativePath, PATHINFO_EXTENSION));
        $fromExt = match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'avif' => 'image/avif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            default => null,
        };

        if (is_string($binary) && $binary !== '' && function_exists('finfo_buffer')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $detected = finfo_buffer($finfo, $binary);
                finfo_close($finfo);
                if (is_string($detected) && str_starts_with($detected, 'image/')) {
                    return $detected;
                }
            }
        }

        return $fromExt ?? 'application/octet-stream';
    }
}

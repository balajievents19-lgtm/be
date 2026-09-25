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

    /**
     * @return array{contents: string, mime: string}
     */
    public function make(?string $binary, string $relativePath, string $mode = self::MODE_DISPLAY): array
    {
        $fallbackMime = $this->guessMime($binary, $relativePath);
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

        $encoded = $this->encode($canvas, $fallbackMime, $relativePath, $mode);
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
    private function encode(GdImage $canvas, string $fallbackMime, string $relativePath, string $mode): array
    {
        $quality = $mode === self::MODE_DOWNLOAD ? 90 : 82;
        $isPng = str_contains($fallbackMime, 'png')
            || str_ends_with(strtolower($relativePath), '.png');
        $isWebp = str_contains($fallbackMime, 'webp')
            || str_ends_with(strtolower($relativePath), '.webp');

        ob_start();
        if ($isPng) {
            imagepng($canvas, null, 6);
            $mime = 'image/png';
        } elseif ($isWebp && function_exists('imagewebp')) {
            imagewebp($canvas, null, $quality);
            $mime = 'image/webp';
        } else {
            imagejpeg($canvas, null, $quality);
            $mime = 'image/jpeg';
        }
        $contents = (string) ob_get_clean();

        return [
            'contents' => $contents,
            'mime' => $mime,
        ];
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

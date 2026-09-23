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
        $this->paintWatermark($canvas, $width, $height, $mode);

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

    private function paintWatermark(GdImage $image, int $width, int $height, string $mode): void
    {
        $label = Brand::NAME;

        if ($mode === self::MODE_DOWNLOAD) {
            $this->paintDownloadWatermark($image, $width, $height, $label);

            return;
        }

        $pad = max(8, (int) round($width * 0.012));
        $shadow = imagecolorallocatealpha($image, 0, 0, 0, 70);
        $ink = imagecolorallocatealpha($image, 255, 255, 255, 55);
        if ($shadow === false || $ink === false) {
            return;
        }

        $font = 5;
        $textWidth = imagefontwidth($font) * strlen($label);
        $textHeight = imagefontheight($font);
        $x = max($pad, $width - $textWidth - $pad);
        $y = max($pad, $height - $textHeight - $pad);

        imagestring($image, $font, $x + 1, $y + 1, $label, $shadow);
        imagestring($image, $font, $x, $y, $label, $ink);
    }

    private function paintDownloadWatermark(GdImage $image, int $width, int $height, string $label): void
    {
        $bandHeight = max(28, (int) round($height * 0.09));
        $band = imagecreatetruecolor($width, $bandHeight);
        if (! $band instanceof GdImage) {
            return;
        }

        imagealphablending($band, false);
        imagesavealpha($band, true);
        $bandFill = imagecolorallocatealpha($band, 14, 17, 35, 55);
        imagefilledrectangle($band, 0, 0, $width - 1, $bandHeight - 1, $bandFill);
        imagealphablending($band, true);

        $scale = max(2, min(10, (int) round($width / 220)));
        $this->drawScaledString($band, $label, 16, max(4, (int) round(($bandHeight - (imagefontheight(5) * $scale)) / 2)), $scale, 255, 255, 255, 20);
        imagecopy($image, $band, 0, $height - $bandHeight, 0, 0, $width, $bandHeight);
        imagedestroy($band);

        $centerScale = max(3, min(14, (int) round($width / 160)));
        $textWidth = imagefontwidth(5) * strlen($label) * $centerScale;
        $textHeight = imagefontheight(5) * $centerScale;
        $cx = max(8, (int) round(($width - $textWidth) / 2));
        $cy = max(8, (int) round(($height - $textHeight) / 2));
        $this->drawScaledString($image, $label, $cx + 2, $cy + 2, $centerScale, 0, 0, 0, 70);
        $this->drawScaledString($image, $label, $cx, $cy, $centerScale, 255, 255, 255, 45);
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

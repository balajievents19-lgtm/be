<?php

namespace App\Support\Media;

use Illuminate\Support\Facades\Storage;

class ResponsiveImageBuilder
{
    /**
     * @param  list<int>  $widths
     * @return array{src: string|null, srcset: string|null, widths: list<int>}
     */
    public function build(?string $path, array $widths = [480, 768, 1024, 1440]): array
    {
        if (! $path) {
            return [
                'src' => null,
                'srcset' => null,
                'widths' => $widths,
            ];
        }

        $base = Storage::disk('public')->url($path);

        $srcset = collect($widths)
            ->map(fn (int $width): string => $base.'?w='.$width.' '.$width.'w')
            ->implode(', ');

        return [
            'src' => $base,
            'srcset' => $srcset,
            'widths' => $widths,
        ];
    }
}

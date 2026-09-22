<?php

namespace App\Support\Media;

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

        return [
            'src' => PublicStorageUrl::make($path),
            'srcset' => null,
            'widths' => $widths,
        ];
    }
}

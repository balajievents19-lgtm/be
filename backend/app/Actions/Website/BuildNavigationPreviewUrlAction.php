<?php

namespace App\Actions\Website;

class BuildNavigationPreviewUrlAction
{
    public function __invoke(string $url): string
    {
        if ($url === '' || str_starts_with($url, '#')) {
            return $url;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return '/'.ltrim($url, '/');
    }
}

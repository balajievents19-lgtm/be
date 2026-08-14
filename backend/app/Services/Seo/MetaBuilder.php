<?php

namespace App\Services\Seo;

use App\Models\Setting;

class MetaBuilder
{
    public function __construct(
        protected Setting $settings,
        protected string $siteUrl,
    ) {}

    /**
     * @param  array{
     *     title?: string|null,
     *     description?: string|null,
     *     keywords?: string|null,
     *     canonical?: string|null,
     *     image?: string|null,
     *     type?: string|null,
     *     robots?: string|null
     * }  $overrides
     * @return array<string, mixed>
     */
    public function build(array $overrides = []): array
    {
        $title = $this->firstFilled(
            $overrides['title'] ?? null,
            $this->settings->meta_title,
            $this->settings->company_name,
            'Balaji Events'
        );

        $description = $this->firstFilled(
            $overrides['description'] ?? null,
            $this->settings->meta_description,
            $this->settings->company_description,
            $this->settings->company_tagline,
            ''
        );

        $keywords = $this->firstFilled(
            $overrides['keywords'] ?? null,
            $this->settings->meta_keywords,
            null
        );

        $canonical = $this->absolute(
            $overrides['canonical'] ?? null
                ?: ($this->settings->canonical_url ?: $this->siteUrl)
        );

        $image = $this->firstFilled(
            $overrides['image'] ?? null,
            $this->settings->imageUrl($this->settings->opengraph_image),
            $this->settings->imageUrl($this->settings->logo),
            null
        );

        $robots = $this->firstFilled(
            $overrides['robots'] ?? null,
            $this->settings->robots,
            config('seo.default_robots', 'index, follow')
        );

        $type = $overrides['type'] ?? 'website';

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'canonical' => $canonical,
            'robots' => $robots,
            'image' => $image,
            'open_graph' => [
                'title' => $title,
                'description' => $description,
                'url' => $canonical,
                'type' => $type,
                'site_name' => $this->settings->company_name ?: 'Balaji Events',
                'image' => $image,
                'locale' => 'en_IN',
            ],
            'twitter' => [
                'card' => config('seo.twitter_card', 'summary_large_image'),
                'title' => $title,
                'description' => $description,
                'image' => $image,
            ],
        ];
    }

    public function absolute(?string $pathOrUrl): string
    {
        if (blank($pathOrUrl) || $pathOrUrl === '/') {
            return rtrim($this->siteUrl, '/');
        }

        if (str_starts_with($pathOrUrl, 'http://') || str_starts_with($pathOrUrl, 'https://')) {
            return rtrim($pathOrUrl, '/');
        }

        return rtrim($this->siteUrl, '/').'/'.ltrim($pathOrUrl, '/');
    }

    private function firstFilled(mixed ...$values): mixed
    {
        foreach ($values as $value) {
            if (filled($value)) {
                return $value;
            }
        }

        return null;
    }
}

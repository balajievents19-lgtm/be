<?php

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\Faq;
use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Models\Service;
use App\Models\Setting;
use App\Support\Brand;
use App\Support\ContentCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SeoService
{
    public const SITEMAP_CACHE_KEY = 'seo.sitemap.xml';

    public const GLOBAL_SCHEMA_CACHE_KEY = 'seo.schema.global';

    protected ?Setting $settings = null;

    protected ?MetaBuilder $metaBuilder = null;

    protected ?SchemaBuilder $schemaBuilder = null;

    public function settings(): Setting
    {
        return $this->settings ??= Setting::singleton();
    }

    /**
     * Public website origin for canonical / OG / JSON-LD / sitemap locs.
     *
     * Precedence: SITE_URL|FRONTEND_URL (config seo.site_url) → settings.canonical_url → APP_URL.
     * Prefer the public frontend origin; do not rely on the API APP_URL in production.
     */
    public function siteUrl(): string
    {
        $settings = $this->settings();

        $url = config('seo.site_url')
            ?: $settings->canonical_url
            ?: config('app.url');

        return rtrim((string) $url, '/');
    }

    public function appUrl(): string
    {
        return rtrim((string) config('app.url'), '/');
    }

    public function meta(): MetaBuilder
    {
        return $this->metaBuilder ??= new MetaBuilder($this->settings(), $this->siteUrl());
    }

    public function schema(): SchemaBuilder
    {
        return $this->schemaBuilder ??= new SchemaBuilder(
            $this->settings(),
            $this->siteUrl(),
            $this->meta()
        );
    }

    /**
     * Global Organization + LocalBusiness + WebSite graph (cached).
     *
     * @return list<array<string, mixed>>
     */
    public function globalSchemas(): array
    {
        $ttl = (int) config('seo.schema_cache_ttl', 3600);

        return Cache::remember(self::GLOBAL_SCHEMA_CACHE_KEY, $ttl, function () {
            return [
                $this->schema()->organization(),
                $this->schema()->localBusiness(),
                $this->schema()->webSite(),
            ];
        });
    }

    /**
     * Default website meta + global schemas.
     *
     * @return array{meta: array<string, mixed>, schema: list<array<string, mixed>>}
     */
    public function forWebsite(): array
    {
        return [
            'meta' => $this->meta()->build([
                'canonical' => $this->siteUrl(),
                'type' => 'website',
            ]),
            'schema' => $this->globalSchemas(),
        ];
    }

    /**
     * @return array{meta: array<string, mixed>, schema: list<array<string, mixed>>}
     */
    public function forHome(): array
    {
        return ContentCache::remember(ContentCache::SEO_HOME, function () {
            $faqs = Faq::query()->active()->homepage()->ordered()->get();
            $settings = $this->settings();

            return [
                'meta' => $this->meta()->build([
                    'title' => $settings->homepage_seo_title,
                    'description' => $settings->homepage_seo_description,
                    'keywords' => $settings->homepage_seo_keywords,
                    'canonical' => '/',
                    'type' => 'website',
                ]),
                'schema' => array_values(array_filter([
                    ...$this->globalSchemas(),
                    $this->schema()->breadcrumb([
                        ['name' => 'Home', 'url' => '/'],
                    ]),
                    $this->schema()->faqPage($faqs),
                ])),
            ];
        });
    }

    /**
     * @return array{meta: array<string, mixed>, schema: list<array<string, mixed>>}
     */
    public function forAbout(): array
    {
        $settings = $this->settings();

        return $this->forStaticPage(
            '/about',
            'About Us',
            $settings->company_description ?: $settings->meta_description,
            $settings->imageUrl($settings->about_image)
        );
    }

    /**
     * @return array{meta: array<string, mixed>, schema: list<array<string, mixed>>}
     */
    public function forServicesIndex(): array
    {
        return $this->forStaticPage(
            '/services',
            'Services',
            'Explore '.$this->brand().' wedding and event services across Rajasthan.'
        );
    }

    /**
     * @return array{meta: array<string, mixed>, schema: list<array<string, mixed>>}
     */
    public function forService(Service $service): array
    {
        $path = '/services/'.$service->slug;

        return [
            'meta' => $this->meta()->build([
                'title' => $service->seo_title ?: ($service->name.' | '.(Brand::name($this->settings()->company_name))),
                'description' => $service->seo_description ?: $service->short_description,
                'keywords' => $service->seo_keywords,
                'canonical' => $path,
                'image' => $service->imageUrl($service->opengraph_image)
                    ?: $service->imageUrl($service->featured_image),
                'type' => 'website',
            ]),
            'schema' => [
                ...$this->globalSchemas(),
                $this->schema()->breadcrumb([
                    ['name' => 'Home', 'url' => '/'],
                    ['name' => 'Services', 'url' => '/services'],
                    ['name' => $service->name, 'url' => $path],
                ]),
                $this->schema()->service($service),
            ],
        ];
    }

    /**
     * @return array{meta: array<string, mixed>, schema: list<array<string, mixed>>}
     */
    public function forPackages(): array
    {
        return $this->forStaticPage(
            '/packages',
            'Packages',
            'Explore '.$this->brand().' wedding and celebration packages across Rajasthan.'
        );
    }

    /**
     * @return array{meta: array<string, mixed>, schema: list<array<string, mixed>>}
     */
    public function forGalleryIndex(): array
    {
        return $this->forStaticPage(
            '/gallery',
            'Gallery',
            'Browse '.$this->brand().' gallery photos from weddings and celebrations by category.'
        );
    }

    /**
     * Category-first gallery SEO for /gallery/{slug}.
     *
     * @return array{meta: array<string, mixed>, schema: list<array<string, mixed>>}
     */
    public function forGalleryCategory(GalleryCategory $category): array
    {
        $path = '/gallery/'.$category->slug;
        $company = Brand::name($this->settings()->company_name);

        $cover = $category->relationLoaded('items')
            ? $category->items->first()
            : $category->items()->active()->ordered()->first();

        $image = null;
        if ($cover instanceof GalleryItem) {
            $image = $cover->imageUrl($cover->opengraph_image)
                ?: $cover->imageUrl($cover->thumbnail ?: $cover->image);
        }

        return [
            'meta' => $this->meta()->build([
                'title' => $category->name.' Gallery | '.$company,
                'description' => $category->description
                    ?: ('Browse '.$category->name.' photos from '.$company.'.'),
                'canonical' => $path,
                'image' => $image,
                'type' => 'website',
            ]),
            'schema' => [
                ...$this->globalSchemas(),
                $this->schema()->breadcrumb([
                    ['name' => 'Home', 'url' => '/'],
                    ['name' => 'Gallery', 'url' => '/gallery'],
                    ['name' => $category->name, 'url' => $path],
                ]),
            ],
        ];
    }

    /**
     * Legacy gallery item SEO (API compatibility). Public routes are category-first.
     *
     * @return array{meta: array<string, mixed>, schema: list<array<string, mixed>>}
     */
    public function forGalleryItem(GalleryItem $item): array
    {
        $path = '/gallery/'.$item->slug;
        $image = $item->imageUrl($item->image);

        return [
            'meta' => $this->meta()->build([
                'title' => $item->seo_title ?: ($item->title.' | Gallery | '.(Brand::name($this->settings()->company_name))),
                'description' => $item->seo_description ?: ($item->caption ?: $item->description),
                'canonical' => $path,
                'image' => $item->imageUrl($item->opengraph_image) ?: $image,
                'type' => 'website',
            ]),
            'schema' => array_values(array_filter([
                ...$this->globalSchemas(),
                $this->schema()->breadcrumb([
                    ['name' => 'Home', 'url' => '/'],
                    ['name' => 'Gallery', 'url' => '/gallery'],
                    ['name' => $item->title, 'url' => $path],
                ]),
                $image ? $this->schema()->galleryImage($item) : null,
            ])),
        ];
    }

    /**
     * @return array{meta: array<string, mixed>, schema: list<array<string, mixed>>}
     */
    public function forBlogIndex(): array
    {
        return $this->forStaticPage(
            '/blog',
            'Blog',
            'Latest news and updates from '.$this->brand().'.'
        );
    }

    /**
     * @return array{meta: array<string, mixed>, schema: list<array<string, mixed>>}
     */
    public function forBlogPost(BlogPost $post): array
    {
        $path = '/blog/'.$post->slug;

        return [
            'meta' => $this->meta()->build([
                'title' => $post->seo_title ?: ($post->title.' | '.(Brand::name($this->settings()->company_name))),
                'description' => $post->seo_description ?: $post->excerpt,
                'keywords' => $post->seo_keywords
                    ?: (is_array($post->tags) ? implode(', ', $post->tags) : null),
                'canonical' => $post->canonical_url ?: $path,
                'image' => $post->imageUrl($post->opengraph_image)
                    ?: $post->imageUrl($post->featured_image),
                'type' => 'article',
            ]),
            'schema' => [
                ...$this->globalSchemas(),
                $this->schema()->breadcrumb([
                    ['name' => 'Home', 'url' => '/'],
                    ['name' => 'Blog', 'url' => '/blog'],
                    ['name' => $post->title, 'url' => $path],
                ]),
                $this->schema()->article($post),
            ],
        ];
    }

    /**
     * @param  iterable<Faq>|null  $faqs
     * @return array{meta: array<string, mixed>, schema: list<array<string, mixed>>}
     */
    public function forFaqPage(?iterable $faqs = null): array
    {
        $faqs ??= Faq::query()->active()->ordered()->get();

        return [
            'meta' => $this->meta()->build([
                'title' => 'FAQ’s | '.(Brand::name($this->settings()->company_name)),
                'description' => 'Frequently asked questions about '.$this->brand().' services, location, and contact details.',
                'canonical' => '/faq',
                'type' => 'website',
            ]),
            'schema' => array_values(array_filter([
                ...$this->globalSchemas(),
                $this->schema()->breadcrumb([
                    ['name' => 'Home', 'url' => '/'],
                    ['name' => 'FAQ’s', 'url' => '/faq'],
                ]),
                $this->schema()->faqPage($faqs),
            ])),
        ];
    }

    /**
     * @return array{meta: array<string, mixed>, schema: list<array<string, mixed>>}
     */
    public function forFaqDetail(Faq $faq): array
    {
        $path = '/faq/'.$faq->slug;
        $company = Brand::name($this->settings()->company_name);
        $answer = $faq->plainAnswer();

        return [
            'meta' => $this->meta()->build([
                'title' => $faq->seo_title ?: ($faq->question.' | '.$company.' FAQ'),
                'description' => $faq->seo_description ?: (Str::limit($answer, 160) ?: null),
                'canonical' => $path,
                'type' => 'website',
            ]),
            'schema' => array_values(array_filter([
                ...$this->globalSchemas(),
                $this->schema()->breadcrumb([
                    ['name' => 'Home', 'url' => '/'],
                    ['name' => 'FAQ’s', 'url' => '/faq'],
                    ['name' => $faq->question, 'url' => $path],
                ]),
                $this->schema()->faqPage([$faq]),
            ])),
        ];
    }

    /**
     * @return array{meta: array<string, mixed>, schema: list<array<string, mixed>>}
     */
    public function forContact(): array
    {
        $settings = $this->settings();

        return $this->forStaticPage(
            '/contact',
            'Contact Us',
            $settings->meta_description
                ?: $settings->company_description
                ?: 'Contact '.$this->brand().'. Phone, address, email, and contact form.'
        );
    }

    /**
     * @return array{meta: array<string, mixed>, schema: list<array<string, mixed>>}
     */
    public function forEvents(): array
    {
        return $this->forStaticPage(
            '/events',
            'Events',
            'Wedding and celebration event highlights from '.$this->brand().' across Rajasthan.'
        );
    }

    /**
     * @return array{meta: array<string, mixed>, schema: list<array<string, mixed>>}
     */
    public function forMedia(): array
    {
        return $this->forStaticPage(
            '/media',
            'Videos & Media',
            'Watch '.$this->brand().' event highlights on YouTube and social platforms.'
        );
    }

    /**
     * @return array{meta: array<string, mixed>, schema: list<array<string, mixed>>}
     */
    public function forPrivacy(): array
    {
        return $this->forStaticPage(
            '/privacy-policy',
            'Privacy Policy',
            'How '.$this->brand().' uses enquiry and customer information.'
        );
    }

    /**
     * @return array{meta: array<string, mixed>, schema: list<array<string, mixed>>}
     */
    public function forTerms(): array
    {
        return $this->forStaticPage(
            '/terms',
            'Terms & Conditions',
            'Website terms for using '.$this->brand().' online services and enquiry forms.'
        );
    }

    /**
     * @return array{meta: array<string, mixed>, schema: list<array<string, mixed>>}
     */
    public function forStaticPage(
        string $path,
        string $title,
        ?string $description = null,
        ?string $image = null,
    ): array {
        $normalized = '/'.ltrim($path === '/' ? '' : $path, '/');
        if ($normalized !== '/') {
            $normalized = rtrim($normalized, '/') ?: '/';
        }

        return [
            'meta' => $this->meta()->build([
                'title' => $title.' | '.(Brand::name($this->settings()->company_name)),
                'description' => $description,
                'canonical' => $normalized === '/' ? $this->siteUrl() : $normalized,
                'image' => $image,
                'type' => 'website',
            ]),
            'schema' => [
                ...$this->globalSchemas(),
                $this->schema()->breadcrumb([
                    ['name' => 'Home', 'url' => '/'],
                    ['name' => $title, 'url' => $normalized],
                ]),
            ],
        ];
    }

    public function sitemapXml(): string
    {
        if ($this->settings()->sitemap_enabled === false) {
            return '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>';
        }

        $ttl = (int) config('seo.sitemap_cache_ttl', 3600);

        return Cache::remember(self::SITEMAP_CACHE_KEY, $ttl, function () {
            return $this->buildSitemapXml();
        });
    }

    public function robotsTxt(): string
    {
        $settings = $this->settings();
        $sitemapHost = $this->siteUrl() ?: $this->appUrl();
        $robots = $settings->robots ?: config('seo.default_robots', 'index, follow');
        $allowIndexing = ! Str::contains(Str::lower($robots), 'noindex');

        $lines = [
            'User-agent: *',
            $allowIndexing ? 'Allow: /' : 'Disallow: /',
            'Disallow: /admin',
            'Disallow: /admin/',
            'Disallow: /livewire',
            'Disallow: /livewire/',
            '',
        ];

        if ($settings->sitemap_enabled !== false) {
            $lines[] = 'Sitemap: '.$sitemapHost.'/sitemap.xml';
            $lines[] = '';
        }

        $extra = trim((string) $settings->robots_txt_extra);
        if ($extra !== '') {
            $lines[] = $extra;
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    public function clearCache(): void
    {
        Cache::forget(self::SITEMAP_CACHE_KEY);
        Cache::forget(self::GLOBAL_SCHEMA_CACHE_KEY);
        Cache::forget(ContentCache::SEO_HOME);
        $this->settings = null;
        $this->metaBuilder = null;
        $this->schemaBuilder = null;
    }

    private function buildSitemapXml(): string
    {
        $urls = [];

        foreach (config('seo.static_pages', []) as $page) {
            $urls[] = [
                'loc' => $this->meta()->absolute($page['path'] ?? '/'),
                'changefreq' => $page['changefreq'] ?? 'monthly',
                'priority' => $page['priority'] ?? '0.5',
                'lastmod' => now()->toAtomString(),
            ];
        }

        Service::query()
            ->active()
            ->ordered()
            ->select(['id', 'slug', 'updated_at'])
            ->each(function (Service $service) use (&$urls): void {
                $urls[] = [
                    'loc' => $this->meta()->absolute('/services/'.$service->slug),
                    'changefreq' => 'weekly',
                    'priority' => '0.8',
                    'lastmod' => optional($service->updated_at)->toAtomString() ?: now()->toAtomString(),
                ];
            });

        // Category-first public gallery routes: /gallery/{category-slug}
        GalleryCategory::query()
            ->active()
            ->ordered()
            ->select(['id', 'slug', 'updated_at'])
            ->each(function (GalleryCategory $category) use (&$urls): void {
                $urls[] = [
                    'loc' => $this->meta()->absolute('/gallery/'.$category->slug),
                    'changefreq' => 'weekly',
                    'priority' => '0.6',
                    'lastmod' => optional($category->updated_at)->toAtomString() ?: now()->toAtomString(),
                ];
            });

        BlogPost::query()
            ->published()
            ->ordered()
            ->select(['id', 'slug', 'updated_at', 'published_at', 'status'])
            ->each(function (BlogPost $post) use (&$urls): void {
                $urls[] = [
                    'loc' => $this->meta()->absolute('/blog/'.$post->slug),
                    'changefreq' => 'weekly',
                    'priority' => '0.7',
                    'lastmod' => optional($post->updated_at)->toAtomString() ?: now()->toAtomString(),
                ];
            });

        Faq::query()
            ->active()
            ->ordered()
            ->select(['id', 'slug', 'updated_at', 'status'])
            ->each(function (Faq $faq) use (&$urls): void {
                $urls[] = [
                    'loc' => $this->meta()->absolute('/faq/'.$faq->slug),
                    'changefreq' => 'monthly',
                    'priority' => '0.5',
                    'lastmod' => optional($faq->updated_at)->toAtomString() ?: now()->toAtomString(),
                ];
            });

        $xml = ['<?xml version="1.0" encoding="UTF-8"?>'];
        $xml[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $url) {
            $xml[] = '  <url>';
            $xml[] = '    <loc>'.e($url['loc']).'</loc>';
            $xml[] = '    <lastmod>'.e($url['lastmod']).'</lastmod>';
            $xml[] = '    <changefreq>'.e($url['changefreq']).'</changefreq>';
            $xml[] = '    <priority>'.e($url['priority']).'</priority>';
            $xml[] = '  </url>';
        }

        $xml[] = '</urlset>';

        return implode("\n", $xml)."\n";
    }

    private function brand(): string
    {
        return Brand::name($this->settings()->company_name);
    }
}

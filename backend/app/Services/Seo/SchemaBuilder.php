<?php

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\Service;
use App\Models\Setting;
use App\Support\Brand;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SchemaBuilder
{
    public function __construct(
        protected Setting $settings,
        protected string $siteUrl,
        protected MetaBuilder $meta,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function organization(): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            '@id' => $this->siteUrl.'/#organization',
            'name' => Brand::name($this->settings->company_name),
            'url' => $this->siteUrl,
            'description' => Brand::rewrite($this->settings->company_description)
                ?: Brand::rewrite($this->settings->meta_description),
        ];

        if ($logo = $this->meta->absoluteMedia($this->settings->imageUrl($this->settings->logo))) {
            $schema['logo'] = $logo;
            $schema['image'] = $logo;
        }

        if ($email = $this->settings->email) {
            $schema['email'] = $email;
        }

        if ($phone = $this->settings->phone) {
            $schema['telephone'] = $phone;
        }

        $sameAs = $this->socialLinks();
        if ($sameAs !== []) {
            $schema['sameAs'] = $sameAs;
        }

        return $this->filterNull($schema);
    }

    /**
     * @return array<string, mixed>
     */
    public function localBusiness(): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            '@id' => $this->siteUrl.'/#localbusiness',
            'name' => Brand::name($this->settings->company_name),
            'url' => $this->siteUrl,
            'description' => Brand::rewrite($this->settings->company_description)
                ?: Brand::rewrite($this->settings->meta_description),
        ];

        if ($logo = $this->meta->absoluteMedia($this->settings->imageUrl($this->settings->logo))) {
            $schema['image'] = $logo;
            $schema['logo'] = $logo;
        }

        if ($phone = $this->settings->phone) {
            $schema['telephone'] = $phone;
        }

        if ($email = $this->settings->email) {
            $schema['email'] = $email;
        }

        // Only real CMS address — do not invent coordinates or postal codes.
        if ($address = $this->settings->address) {
            $postal = [
                '@type' => 'PostalAddress',
                'streetAddress' => $address,
                'addressCountry' => 'IN',
            ];

            $haystack = $address;
            if (stripos($haystack, 'Jhunjhunu') !== false) {
                $postal['addressLocality'] = 'Jhunjhunu';
            }
            if (stripos($haystack, 'Rajasthan') !== false) {
                $postal['addressRegion'] = 'Rajasthan';
            }

            $schema['address'] = $postal;
        }

        $areaServed = $this->areaServed();
        if ($areaServed !== []) {
            $schema['areaServed'] = $areaServed;
        }

        if ($hours = $this->settings->working_hours) {
            $schema['openingHours'] = preg_split("/\r\n|\n|\r/", trim($hours)) ?: [];
        }

        $sameAs = $this->socialLinks();
        if ($sameAs !== []) {
            $schema['sameAs'] = $sameAs;
        }

        return $this->filterNull($schema);
    }

    /**
     * @return array<string, mixed>
     */
    public function webSite(): array
    {
        return $this->filterNull([
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            '@id' => $this->siteUrl.'/#website',
            'name' => Brand::name($this->settings->company_name),
            'url' => $this->siteUrl,
            'description' => Brand::rewrite($this->settings->meta_description)
                ?: Brand::rewrite($this->settings->company_description),
            'publisher' => [
                '@id' => $this->siteUrl.'/#organization',
            ],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => $this->siteUrl.'/services?search={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ]);
    }

    /**
     * @param  list<array{name: string, url?: string|null}>  $items
     * @return array<string, mixed>
     */
    public function breadcrumb(array $items): array
    {
        $list = [];

        foreach (array_values($items) as $index => $item) {
            $entry = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
            ];

            if (! empty($item['url'])) {
                $entry['item'] = $this->meta->absolute($item['url']);
            }

            $list[] = $entry;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $list,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function article(BlogPost $post): array
    {
        $url = $this->meta->absolute('/blog/'.$post->slug);
        $image = $post->imageUrl($post->featured_image)
            ?: $post->imageUrl($post->opengraph_image);

        return $this->filterNull([
            '@context' => 'https://schema.org',
            '@type' => $post->schema_type ?: 'BlogPosting',
            'headline' => $post->seo_title ?: $post->title,
            'description' => $post->seo_description ?: $post->excerpt,
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at?->toIso8601String(),
            'author' => [
                '@type' => 'Person',
                'name' => $post->author ?: (Brand::name($this->settings->company_name)),
            ],
            'publisher' => [
                '@id' => $this->siteUrl.'/#organization',
            ],
            'mainEntityOfPage' => $url,
            'url' => $url,
            'image' => $image ? $this->imageObject((string) $this->meta->absoluteMedia($image), $post->title) : null,
            'keywords' => is_array($post->tags) ? implode(', ', $post->tags) : $post->seo_keywords,
            'wordCount' => str_word_count(strip_tags((string) $post->content)),
            'articleSection' => $post->category?->name,
        ]);
    }

    /**
     * @param  Collection<int, Faq>|iterable<Faq>  $faqs
     * @return array<string, mixed>
     */
    public function faqPage(iterable $faqs): array
    {
        $entities = [];

        foreach ($faqs as $faq) {
            $answer = method_exists($faq, 'plainAnswer')
                ? $faq->plainAnswer()
                : trim(html_entity_decode(strip_tags((string) $faq->answer)));

            if (blank($faq->question) || blank($answer)) {
                continue;
            }

            $entities[] = [
                '@type' => 'Question',
                'name' => Brand::rewrite($faq->question),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => Brand::rewrite($answer),
                ],
            ];
        }

        if ($entities === []) {
            return [];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $entities,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function imageObject(string $url, ?string $name = null, ?string $caption = null): array
    {
        return $this->filterNull([
            '@context' => 'https://schema.org',
            '@type' => 'ImageObject',
            'contentUrl' => $url,
            'url' => $url,
            'name' => Brand::rewrite($name),
            'caption' => Brand::rewrite($caption),
            'creditText' => Brand::name($this->settings->company_name),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function service(Service $service): array
    {
        $url = $this->meta->absolute('/services/'.$service->slug);
        $image = $this->meta->absoluteMedia($service->imageUrl($service->featured_image));

        return $this->filterNull([
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => $service->name,
            'description' => $service->seo_description ?: $service->short_description,
            'url' => $url,
            'provider' => [
                '@id' => $this->siteUrl.'/#localbusiness',
            ],
            'image' => $image,
            'areaServed' => [
                '@type' => 'AdministrativeArea',
                'name' => 'Rajasthan',
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function galleryImage(GalleryItem $item): array
    {
        $url = $this->meta->absoluteMedia($item->imageUrl($item->image));
        $pageUrl = $this->meta->absolute('/gallery/'.$item->slug);

        return $this->filterNull([
            ...$this->imageObject(
                (string) $url,
                $item->title,
                $item->caption ?: $item->alt_text
            ),
            'url' => $pageUrl,
        ]);
    }

    /**
     * Generic Event schema for promotions / bookings.
     *
     * @param  array{
     *     name: string,
     *     description?: string|null,
     *     startDate: string,
     *     endDate?: string|null,
     *     url?: string|null,
     *     image?: string|null,
     *     locationName?: string|null,
     *     locationAddress?: string|null
     * }  $event
     * @return array<string, mixed>
     */
    public function event(array $event): array
    {
        $location = null;
        if (! empty($event['locationName']) || ! empty($event['locationAddress'])) {
            $location = $this->filterNull([
                '@type' => 'Place',
                'name' => $event['locationName'] ?? (Brand::name($this->settings->company_name)),
                'address' => $event['locationAddress'] ?? $this->settings->address,
            ]);
        }

        return $this->filterNull([
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $event['name'],
            'description' => $event['description'] ?? null,
            'startDate' => $event['startDate'],
            'endDate' => $event['endDate'] ?? null,
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'eventStatus' => 'https://schema.org/EventScheduled',
            'url' => isset($event['url']) ? $this->meta->absolute($event['url']) : $this->siteUrl,
            'image' => $this->meta->absoluteMedia(
                $event['image'] ?? $this->settings->imageUrl($this->settings->opengraph_image)
            ),
            'location' => $location,
            'organizer' => [
                '@id' => $this->siteUrl.'/#organization',
            ],
        ]);
    }

    /**
     * @return list<string>
     */
    private function socialLinks(): array
    {
        return collect([
            $this->settings->facebook,
            $this->settings->instagram,
            $this->settings->youtube,
            $this->settings->linkedin,
            $this->settings->twitter,
        ])->filter(function (?string $url): bool {
            if (! filled($url) || ! Str::startsWith($url, ['http://', 'https://'])) {
                return false;
            }

            $host = strtolower((string) parse_url($url, PHP_URL_HOST));
            $path = trim((string) parse_url($url, PHP_URL_PATH), '/');

            $placeholderHosts = [
                'facebook.com',
                'www.facebook.com',
                'linkedin.com',
                'www.linkedin.com',
                'x.com',
                'www.x.com',
                'twitter.com',
                'www.twitter.com',
                'youtube.com',
                'www.youtube.com',
                'instagram.com',
                'www.instagram.com',
            ];

            if ($path === '' && in_array($host, $placeholderHosts, true)) {
                return false;
            }

            return true;
        })
            ->values()
            ->all();
    }

    /**
     * Cities/state already named in CMS copy — never invent locations.
     *
     * @return list<array{ '@type': string, name: string }>
     */
    private function areaServed(): array
    {
        $haystack = strtolower(implode(' ', array_filter([
            (string) $this->settings->company_description,
            (string) $this->settings->meta_description,
            (string) $this->settings->meta_keywords,
            (string) $this->settings->homepage_seo_keywords,
            (string) $this->settings->address,
        ])));

        $places = [
            'Jhunjhunu' => 'City',
            'Mandawa' => 'City',
            'Alsisar' => 'City',
            'Jaipur' => 'City',
            'Udaipur' => 'City',
            'Rajasthan' => 'State',
        ];

        $served = [];
        foreach ($places as $name => $type) {
            if (str_contains($haystack, strtolower($name))) {
                $served[] = [
                    '@type' => $type,
                    'name' => $name,
                ];
            }
        }

        return $served;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function filterNull(array $data): array
    {
        return array_filter(
            $data,
            static fn (mixed $value) => $value !== null && $value !== '' && $value !== []
        );
    }
}

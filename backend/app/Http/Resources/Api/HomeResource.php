<?php

namespace App\Http\Resources\Api;

use App\Models\BlogPost;
use App\Models\CtaSection;
use App\Models\EventOverview;
use App\Models\EventType;
use App\Models\ExternalMedia;
use App\Models\Faq;
use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Models\HeroSlide;
use App\Models\OfficeLocation;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Statistic;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @property-read array{
 *     settings: Setting,
 *     header_navigation: list<array<string, mixed>>,
 *     footer_navigation: list<array<string, mixed>>,
 *     hero: Collection<int, HeroSlide>,
 *     featured_services: Collection<int, Service>,
 *     events_overview: Collection<int, EventOverview>,
 *     featured_gallery: Collection<int, GalleryItem>,
 *     gallery_categories: Collection<int, GalleryCategory>,
 *     external_media: Collection<int, ExternalMedia>,
 *     event_types: Collection<int, EventType>,
 *     testimonials: Collection<int, Testimonial>,
 *     success_stories: Collection<int, Testimonial>,
 *     featured_blog: Collection<int, BlogPost>,
 *     featured_faqs: Collection<int, Faq>,
 *     statistics: Collection<int, Statistic>,
 *     cta_sections: Collection<int, CtaSection>,
 *     team_members: Collection<int, TeamMember>,
 *     office_locations: Collection<int, OfficeLocation>,
 *     google_reviews: array<string, mixed>,
 *     sections: array<string, bool>
 * } $resource
 */
class HomeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $featuredFaqs = $this->resource['featured_faqs'];

        return [
            'settings' => new SettingResource($this->resource['settings']),
            'header_navigation' => $this->resource['header_navigation'],
            'footer_navigation' => $this->resource['footer_navigation'],
            'hero' => HeroSlideResource::collection($this->resource['hero']),
            'featured_services' => ServiceResource::collection($this->resource['featured_services']),
            'events_overview' => EventOverviewResource::collection($this->resource['events_overview']),
            'featured_gallery' => GalleryItemResource::collection($this->resource['featured_gallery'] ?? collect()),
            'gallery_categories' => GalleryCategoryResource::collection($this->resource['gallery_categories'] ?? collect()),
            'external_media' => ExternalMediaResource::collection($this->resource['external_media'] ?? collect()),
            'event_types' => EventTypeResource::collection($this->resource['event_types'] ?? collect()),
            'testimonials' => TestimonialResource::collection($this->resource['testimonials']),
            'success_stories' => TestimonialResource::collection($this->resource['success_stories']),
            'featured_blog' => BlogPostResource::collection($this->resource['featured_blog']),
            'featured_faqs' => FaqResource::collection($featuredFaqs),
            'faq_schema' => Faq::faqPageSchema($featuredFaqs),
            'statistics' => StatisticResource::collection($this->resource['statistics'] ?? collect()),
            'cta_sections' => CtaSectionResource::collection($this->resource['cta_sections'] ?? collect()),
            'team_members' => TeamMemberResource::collection($this->resource['team_members'] ?? collect()),
            'office_locations' => OfficeLocationResource::collection($this->resource['office_locations'] ?? collect()),
            'google_reviews' => $this->resource['google_reviews'] ?? [
                'configured' => false,
                'enabled' => false,
                'reviews' => [],
            ],
            'sections' => $this->resource['sections'] ?? [],
        ];
    }
}

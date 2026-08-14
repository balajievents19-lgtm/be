<?php

namespace App\Http\Resources\Api;

use App\Models\BlogPost;
use App\Models\CtaSection;
use App\Models\EventOverview;
use App\Models\Faq;
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
 *     testimonials: Collection<int, Testimonial>,
 *     success_stories: Collection<int, Testimonial>,
 *     featured_blog: Collection<int, BlogPost>,
 *     featured_faqs: Collection<int, Faq>,
 *     statistics: Collection<int, Statistic>,
 *     cta_sections: Collection<int, CtaSection>,
 *     team_members: Collection<int, TeamMember>,
 *     office_locations: Collection<int, OfficeLocation>
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
            'featured_gallery' => GalleryItemResource::collection($this->resource['featured_gallery']),
            'testimonials' => TestimonialResource::collection($this->resource['testimonials']),
            'success_stories' => TestimonialResource::collection($this->resource['success_stories']),
            'featured_blog' => BlogPostResource::collection($this->resource['featured_blog']),
            'featured_faqs' => FaqResource::collection($featuredFaqs),
            'faq_schema' => Faq::faqPageSchema($featuredFaqs),
            'statistics' => StatisticResource::collection($this->resource['statistics'] ?? collect()),
            'cta_sections' => CtaSectionResource::collection($this->resource['cta_sections'] ?? collect()),
            'team_members' => TeamMemberResource::collection($this->resource['team_members'] ?? collect()),
            'office_locations' => OfficeLocationResource::collection($this->resource['office_locations'] ?? collect()),
        ];
    }
}

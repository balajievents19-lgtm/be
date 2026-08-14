<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\HomeResource;
use App\Models\BlogPost;
use App\Models\CtaSection;
use App\Models\EventOverview;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\HeroSlide;
use App\Models\OfficeLocation;
use App\Models\Service;
use App\Models\Statistic;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Services\WebsiteCmsService;
use App\Support\ContentCache;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    public function __construct(
        private readonly WebsiteCmsService $websiteCmsService,
    ) {}

    public function show(): JsonResponse
    {
        $payload = ContentCache::remember(ContentCache::HOME, function () {
            $settings = $this->websiteCmsService->settings();

            $hero = HeroSlide::query()
                ->active()
                ->ordered()
                ->get();

            $featuredServices = Service::query()
                ->active()
                ->homepage()
                ->ordered()
                ->select([
                    'id',
                    'name',
                    'slug',
                    'short_description',
                    'featured_image',
                    'icon',
                    'featured',
                    'show_on_homepage',
                    'sort_order',
                    'status',
                ])
                ->get();

            $eventsOverview = EventOverview::query()
                ->active()
                ->homepage()
                ->ordered()
                ->select([
                    'id',
                    'title',
                    'caption',
                    'description',
                    'image',
                    'link_url',
                    'featured',
                    'homepage_featured',
                    'sort_order',
                    'status',
                ])
                ->get();

            $featuredGallery = GalleryItem::query()
                ->with('category:id,name,slug')
                ->active()
                ->homepage()
                ->ordered()
                ->select([
                    'id',
                    'gallery_category_id',
                    'title',
                    'slug',
                    'image',
                    'thumbnail',
                    'alt_text',
                    'caption',
                    'youtube_url',
                    'vimeo_url',
                    'featured',
                    'homepage_featured',
                    'sort_order',
                    'status',
                ])
                ->get();

            $testimonials = Testimonial::query()
                ->active()
                ->homepage()
                ->clientSays()
                ->ordered()
                ->select([
                    'id',
                    'type',
                    'name',
                    'quote',
                    'body',
                    'avatar',
                    'image',
                    'rating',
                    'video_url',
                    'featured',
                    'homepage_featured',
                    'sort_order',
                    'status',
                ])
                ->get();

            $successStories = Testimonial::query()
                ->active()
                ->homepage()
                ->successStories()
                ->ordered()
                ->select([
                    'id',
                    'type',
                    'name',
                    'quote',
                    'body',
                    'avatar',
                    'image',
                    'rating',
                    'video_url',
                    'featured',
                    'homepage_featured',
                    'sort_order',
                    'status',
                ])
                ->get();

            $featuredBlog = BlogPost::query()
                ->with('category:id,name,slug')
                ->published()
                ->homepage()
                ->ordered()
                ->select([
                    'id',
                    'blog_category_id',
                    'title',
                    'slug',
                    'excerpt',
                    'featured_image',
                    'thumbnail',
                    'alt_text',
                    'featured',
                    'homepage_featured',
                    'published_at',
                    'reading_time',
                    'author',
                    'tags',
                    'status',
                ])
                ->get();

            $featuredFaqs = Faq::query()
                ->with('category:id,name,slug')
                ->active()
                ->homepage()
                ->ordered()
                ->select([
                    'id',
                    'faq_category_id',
                    'question',
                    'slug',
                    'answer',
                    'featured',
                    'homepage_featured',
                    'sort_order',
                    'status',
                ])
                ->get();

            $statistics = Statistic::query()
                ->active()
                ->homepage()
                ->ordered()
                ->get(['id', 'label', 'value', 'icon', 'suffix', 'sort_order', 'show_on_homepage', 'status']);

            $ctaSections = CtaSection::query()
                ->active()
                ->homepage()
                ->ordered()
                ->get();

            $teamMembers = TeamMember::query()
                ->active()
                ->ordered()
                ->get();

            $officeLocations = OfficeLocation::query()
                ->active()
                ->ordered()
                ->get();

            return (new HomeResource([
                'settings' => $settings,
                'header_navigation' => $this->websiteCmsService->headerNavigation(),
                'footer_navigation' => $this->websiteCmsService->footerNavigation(),
                'hero' => $hero,
                'featured_services' => $featuredServices,
                'events_overview' => $eventsOverview,
                'featured_gallery' => $featuredGallery,
                'testimonials' => $testimonials,
                'success_stories' => $successStories,
                'featured_blog' => $featuredBlog,
                'featured_faqs' => $featuredFaqs,
                'statistics' => $statistics,
                'cta_sections' => $ctaSections,
                'team_members' => $teamMembers,
                'office_locations' => $officeLocations,
            ]))->response()->getData(true);
        });

        return response()->json($payload);
    }
}

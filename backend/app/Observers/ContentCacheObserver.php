<?php

namespace App\Observers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\CtaSection;
use App\Models\EventOverview;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Models\HeroSlide;
use App\Models\NavigationItem;
use App\Models\OfficeLocation;
use App\Models\Redirect;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServicePackage;
use App\Models\Setting;
use App\Models\Statistic;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Support\ContentCache;

class ContentCacheObserver
{
    public function saved(mixed $model): void
    {
        $this->flushFor($model);
    }

    public function deleted(mixed $model): void
    {
        $this->flushFor($model);
    }

    public function restored(mixed $model): void
    {
        $this->flushFor($model);
    }

    public function forceDeleted(mixed $model): void
    {
        $this->flushFor($model);
    }

    private function flushFor(mixed $model): void
    {
        $class = $model::class;

        match ($class) {
            Setting::class => ContentCache::flush(ContentCache::SETTINGS),
            HeroSlide::class => ContentCache::flush(ContentCache::HERO),
            NavigationItem::class => ContentCache::flush(
                ContentCache::NAVIGATION_HEADER,
                ContentCache::NAVIGATION_FOOTER
            ),
            Service::class => ContentCache::flush(ContentCache::SERVICES),
            GalleryItem::class,
            GalleryCategory::class => ContentCache::flush(ContentCache::GALLERY, ContentCache::GALLERY_CATEGORIES),
            BlogPost::class,
            BlogCategory::class => ContentCache::flush(ContentCache::BLOG),
            Faq::class,
            FaqCategory::class => ContentCache::flush(ContentCache::FAQS),
            EventOverview::class => ContentCache::flush(ContentCache::EVENT_OVERVIEWS),
            Testimonial::class => ContentCache::flush(ContentCache::TESTIMONIALS),
            TeamMember::class => ContentCache::flush(ContentCache::TEAM),
            Statistic::class => ContentCache::flush(ContentCache::STATISTICS),
            CtaSection::class => ContentCache::flush(ContentCache::CTA),
            OfficeLocation::class => ContentCache::flush(ContentCache::OFFICES),
            ServiceCategory::class => ContentCache::flush(ContentCache::SERVICES, ContentCache::CATEGORIES),
            ServicePackage::class => ContentCache::flush(ContentCache::PACKAGES),
            Redirect::class => ContentCache::flush(ContentCache::REDIRECTS),
            default => ContentCache::flushAll(),
        };
    }
}

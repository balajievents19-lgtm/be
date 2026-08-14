<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\BlogPosts\BlogPostResource;
use App\Filament\Resources\GalleryItems\GalleryItemResource;
use App\Filament\Resources\HeroSlides\HeroSlideResource;
use App\Filament\Resources\Services\ServiceResource;
use App\Models\BlogPost;
use App\Models\GalleryItem;
use App\Models\HeroSlide;
use App\Models\Service;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ContentStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Website overview';

    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return ServiceResource::canViewAny()
            || GalleryItemResource::canViewAny()
            || BlogPostResource::canViewAny()
            || HeroSlideResource::canViewAny();
    }

    protected function getStats(): array
    {
        $stats = [];

        if (ServiceResource::canViewAny()) {
            $stats[] = Stat::make('Services', Service::query()->count())
                ->description('Manage services')
                ->icon(Heroicon::OutlinedBriefcase)
                ->url(ServiceResource::getUrl('index'));
        }

        if (GalleryItemResource::canViewAny()) {
            $stats[] = Stat::make('Gallery', GalleryItem::query()->count())
                ->description('Gallery items')
                ->icon(Heroicon::OutlinedSquares2x2)
                ->url(GalleryItemResource::getUrl('index'));
        }

        if (BlogPostResource::canViewAny()) {
            $stats[] = Stat::make('Blog', BlogPost::query()->count())
                ->description('Blog posts')
                ->icon(Heroicon::OutlinedNewspaper)
                ->url(BlogPostResource::getUrl('index'));
        }

        if (HeroSlideResource::canViewAny()) {
            $stats[] = Stat::make('Hero Slider', HeroSlide::query()->count())
                ->description('Homepage slides')
                ->icon(Heroicon::OutlinedPhoto)
                ->url(HeroSlideResource::getUrl('index'));
        }

        return $stats;
    }
}

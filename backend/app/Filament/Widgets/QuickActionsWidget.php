<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\BlogPosts\BlogPostResource;
use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Filament\Resources\HeroSlides\HeroSlideResource;
use App\Filament\Resources\ServicePackages\ServicePackageResource;
use App\Filament\Resources\Services\ServiceResource;
use Filament\Widgets\Widget;

class QuickActionsWidget extends Widget
{
    protected string $view = 'filament.widgets.quick-actions';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return HeroSlideResource::canCreate()
            || ServiceResource::canCreate()
            || ServiceResource::canViewAny()
            || ServicePackageResource::canViewAny()
            || BlogPostResource::canCreate()
            || ContactInquiryResource::canViewAny()
            || ContactInquiryResource::canCreate();
    }

    /**
     * @return list<array{label: string, url: string}>
     */
    public function getLinks(): array
    {
        $links = [];

        if (ContactInquiryResource::canViewAny()) {
            $links[] = [
                'label' => 'View Leads',
                'url' => ContactInquiryResource::getUrl('index'),
            ];
        }

        if (ContactInquiryResource::canCreate()) {
            $links[] = [
                'label' => 'Add Lead',
                'url' => ContactInquiryResource::getUrl('create'),
            ];
        }

        if (ServiceResource::canViewAny()) {
            $links[] = [
                'label' => 'View Services',
                'url' => ServiceResource::getUrl('index'),
            ];
        }

        if (ServicePackageResource::canViewAny()) {
            $links[] = [
                'label' => 'View Packages',
                'url' => ServicePackageResource::getUrl('index'),
            ];
        }

        if (HeroSlideResource::canCreate()) {
            $links[] = [
                'label' => 'Add Hero Slide',
                'url' => HeroSlideResource::getUrl('create'),
            ];
        }

        if (ServiceResource::canCreate()) {
            $links[] = [
                'label' => 'Add Service',
                'url' => ServiceResource::getUrl('create'),
            ];
        }

        if (BlogPostResource::canCreate()) {
            $links[] = [
                'label' => 'Add Blog Post',
                'url' => BlogPostResource::getUrl('create'),
            ];
        }

        return $links;
    }
}

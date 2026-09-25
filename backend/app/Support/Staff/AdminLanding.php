<?php

namespace App\Support\Staff;

use App\Filament\Pages\AccessNotAssigned;
use App\Filament\Pages\Dashboard;
use App\Filament\Resources\BlogPosts\BlogPostResource;
use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Filament\Resources\GalleryItems\GalleryItemResource;
use App\Filament\Resources\NewsletterSubscribers\NewsletterSubscriberResource;
use App\Filament\Resources\ServicePackages\ServicePackageResource;
use App\Filament\Resources\Services\ServiceResource;
use App\Filament\Resources\StaffPosts\StaffPostResource;
use App\Filament\Resources\Testimonials\TestimonialResource;
use App\Models\User;
use App\Support\Rbac\AdminUserSecurity;
use Illuminate\Support\Facades\Auth;

final class AdminLanding
{
    public static function url(?User $user = null): string
    {
        $user ??= Auth::user();

        if ($user === null) {
            return url('/admin/login');
        }

        if (AdminUserSecurity::isSuperAdmin($user)) {
            return Dashboard::getUrl();
        }

        if (StaffPanelAccess::isRestrictedStaff($user)) {
            return StaffContentAccess::hasAssignedContentScope($user)
                ? StaffPostResource::getUrl()
                : AccessNotAssigned::getUrl();
        }

        if (StaffPanelAccess::isContentManager($user)) {
            return self::firstAuthorizedResourceUrl([
                GalleryItemResource::class,
                BlogPostResource::class,
                ServiceResource::class,
                ServicePackageResource::class,
                TestimonialResource::class,
            ]) ?? Dashboard::getUrl();
        }

        if ($user->can('leads.view')) {
            return ContactInquiryResource::getUrl();
        }

        if ($user->can('newsletter.view')) {
            return NewsletterSubscriberResource::getUrl();
        }

        return Dashboard::getUrl();
    }

    /**
     * @param  list<class-string>  $resources
     */
    private static function firstAuthorizedResourceUrl(array $resources): ?string
    {
        foreach ($resources as $resource) {
            if (! class_exists($resource) || ! method_exists($resource, 'canAccess')) {
                continue;
            }
            if ($resource::canAccess()) {
                return $resource::getUrl();
            }
        }

        return null;
    }
}

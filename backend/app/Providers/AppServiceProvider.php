<?php

namespace App\Providers;

use App\Contracts\NavigationRepository;
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
use App\Models\User;
use App\Observers\ContentCacheObserver;
use App\Repositories\EloquentNavigationRepository;
use App\Services\Seo\SeoService;
use App\Support\Rbac\AdminModules;
use App\Support\Rbac\AdminUserSecurity;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SeoService::class);
        $this->app->bind(NavigationRepository::class, EloquentNavigationRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, string $ability, array $arguments = []) {
            if (! $user instanceof User) {
                return null;
            }

            $model = $arguments[0] ?? null;

            // Hard deny: self-delete and last Super Admin delete (even for Super Admin).
            if ($ability === 'delete' && $model instanceof User) {
                if ($user->is($model)) {
                    return false;
                }

                if (AdminUserSecurity::isLastSuperAdmin($model)) {
                    return false;
                }
            }

            // Hard deny: non-Super Admin attempting assignRoles.
            if ($ability === 'assignRoles' && ! AdminUserSecurity::canManageRoles($user)) {
                return false;
            }

            return $user->hasRole(AdminModules::ROLE_SUPER_ADMIN) ? true : null;
        });

        // Public anonymous API quota. Trusted Nuxt SSR GET/HEAD with a valid
        // X-SSR-Secret use a separate finite limiter (config/ssr.php) so
        // concurrent SSR does not starve public clients — and vice versa.
        // POST contact/newsletter keep their own throttles below; they never
        // receive the SSR read exemption (isMethodSafe() required).
        RateLimiter::for('api', function (Request $request) {
            if (\App\Support\SsrInternalAuth::isTrustedRead($request)) {
                $perMinute = max(1, (int) config('ssr.rate_limit_per_minute', 300));

                return Limit::perMinute($perMinute)->by('ssr:'.$request->ip());
            }

            return Limit::perMinute(60)->by($request->ip());
        });

        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('newsletter', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        $observer = ContentCacheObserver::class;

        Setting::observe($observer);
        HeroSlide::observe($observer);
        NavigationItem::observe($observer);
        Service::observe($observer);
        GalleryItem::observe($observer);
        GalleryCategory::observe($observer);
        BlogPost::observe($observer);
        BlogCategory::observe($observer);
        Faq::observe($observer);
        FaqCategory::observe($observer);
        EventOverview::observe($observer);
        Testimonial::observe($observer);
        TeamMember::observe($observer);
        Statistic::observe($observer);
        CtaSection::observe($observer);
        OfficeLocation::observe($observer);
        ServiceCategory::observe($observer);
        ServicePackage::observe($observer);
        Redirect::observe($observer);
    }
}

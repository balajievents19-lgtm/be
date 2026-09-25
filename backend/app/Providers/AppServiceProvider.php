<?php

namespace App\Providers;

use App\Contracts\NavigationRepository;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\CtaSection;
use App\Models\EventOverview;
use App\Models\EventType;
use App\Models\ExternalMedia;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Models\HeroSlide;
use App\Models\HomepageSection;
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
use App\Observers\GalleryOriginalObserver;
use App\Repositories\EloquentNavigationRepository;
use App\Services\Customer\EmailVerificationService;
use App\Services\Seo\SeoService;
use App\Support\Media\AdminPreviewMedia;
use App\Support\Rbac\AdminModules;
use App\Support\Rbac\AdminUserSecurity;
use App\Support\SsrInternalAuth;
use App\Filament\Auth\AdminLoginResponse;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use Illuminate\Auth\Middleware\Authenticate;
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
        $this->app->singleton(EmailVerificationService::class);
        $this->app->bind(LoginResponseContract::class, AdminLoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Livewire temp files must not use the public disk. FILESYSTEM_DISK=public
        // would otherwise publish livewire-tmp under /storage and break FilePond previews.
        config([
            'livewire.temporary_file_upload.disk' => 'local',
        ]);

        FileUpload::configureUsing(function (FileUpload $component): void {
            $component->getUploadedFileUsing(function (BaseFileUpload $component, string $file, string|array|null $storedFileNames): ?array {
                return AdminPreviewMedia::uploadedFilePayload($component, $file, $storedFileNames);
            });
        });
        // API customer routes must return 401 JSON, not redirect to a missing web "login" route.
        Authenticate::redirectUsing(function (Request $request): ?string {
            if ($request->is('api/*') || $request->expectsJson()) {
                return null;
            }

            return '/admin/login';
        });

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
            if (SsrInternalAuth::isTrustedRead($request)) {
                $perMinute = max(1, (int) config('ssr.rate_limit_per_minute', 300));

                return Limit::perMinute($perMinute)->by('ssr:'.$request->ip());
            }

            return Limit::perMinute(60)->by($request->ip());
        });

        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('geo', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });

        RateLimiter::for('newsletter', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('customer-login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('customer-register', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('customer-forgot-password', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('customer-oauth', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('customer-email-verify', function (Request $request) {
            $email = strtolower((string) $request->input('email', $request->route('id', '')));

            return Limit::perMinute(8)->by($request->ip().'|'.$email);
        });

        $observer = ContentCacheObserver::class;

        Setting::observe($observer);
        HeroSlide::observe($observer);
        HomepageSection::observe($observer);
        NavigationItem::observe($observer);
        Service::observe($observer);
        GalleryItem::observe($observer);
        GalleryItem::observe(GalleryOriginalObserver::class);
        GalleryCategory::observe($observer);
        ExternalMedia::observe($observer);
        EventType::observe($observer);
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

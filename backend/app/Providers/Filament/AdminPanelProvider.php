<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Http\Middleware\DenyCustomerAdminAccess;
use App\Http\Middleware\RestrictStaffAdminPanel;
use App\Support\Staff\AdminLanding;
use App\Models\Setting;
use App\Support\Media\PublicStorageUrl;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->homeUrl(fn (): string => AdminLanding::url())
            ->brandName(\App\Support\Brand::NAME)
            ->favicon(function (): ?string {
                try {
                    $path = Setting::query()->value('favicon');
                } catch (\Throwable) {
                    return null;
                }

                return is_string($path) && $path !== ''
                    ? PublicStorageUrl::make($path)
                    : null;
            })
            ->colors([
                'primary' => Color::hex('#f15b22'),
            ])
            ->navigationGroups([
                NavigationGroup::make('Website')->collapsed(false),
                NavigationGroup::make('CRM')->collapsed(false),
                NavigationGroup::make('Users')->collapsed(false),
            ])
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                DenyCustomerAdminAccess::class,
                RestrictStaffAdminPanel::class,
            ]);
    }
}

<?php

use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\Customer\AccountSecurityController;
use App\Http\Controllers\Api\Customer\AuthController as CustomerAuthController;
use App\Http\Controllers\Api\Customer\OAuthController;
use App\Http\Controllers\Api\Customer\EmailVerificationController;
use App\Http\Controllers\Api\Customer\SessionController;
use App\Http\Controllers\Api\Customer\SocialAccountController;
use App\Http\Controllers\Api\EventOverviewController;
use App\Http\Controllers\Api\EventTypeController;
use App\Http\Controllers\Api\ExternalMediaController;
use App\Http\Controllers\Api\GeoController;
use App\Http\Controllers\Api\GoogleReviewsController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\GalleryController;
use App\Http\Controllers\Api\GalleryDownloadController;
use App\Http\Controllers\Api\HeroSlideController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\NavigationController;
use App\Http\Controllers\Api\NewsletterController;
use App\Http\Controllers\Api\SeoController;
use App\Http\Controllers\Api\ServiceCatalogController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\TestimonialController;
use Illuminate\Support\Facades\Route;

Route::get('/home', [HomeController::class, 'show'])->name('api.home');
Route::get('/navigation', [NavigationController::class, 'index'])->name('api.navigation.index');
Route::get('/hero-slides', [HeroSlideController::class, 'index']);
Route::get('/settings', [SettingController::class, 'show']);
Route::get('/seo', [SeoController::class, 'show'])->name('api.seo.show');
Route::get('/seo/resolve', [SeoController::class, 'resolve'])->name('api.seo.resolve');
Route::get('/services', [ServiceController::class, 'index'])->name('api.services.index');
Route::get('/services/{slug}', [ServiceController::class, 'show'])->name('api.services.show');
Route::get('/service-categories', [ServiceCatalogController::class, 'categories'])->name('api.service-categories.index');
Route::get('/service-packages', [ServiceCatalogController::class, 'packages'])->name('api.service-packages.index');
Route::get('/gallery', [GalleryController::class, 'index'])->name('api.gallery.index');
Route::get('/gallery/categories', [GalleryController::class, 'categories'])->name('api.gallery.categories');
Route::get('/gallery/categories/{slug}', [GalleryController::class, 'category'])->name('api.gallery.categories.show');
Route::get('/gallery/{slug}', [GalleryController::class, 'show'])->name('api.gallery.show');
Route::get('/blog', [BlogController::class, 'index'])->name('api.blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('api.blog.show');
Route::get('/faqs', [FaqController::class, 'index'])->name('api.faqs.index');
Route::get('/faqs/{slug}', [FaqController::class, 'show'])->name('api.faqs.show');
Route::get('/event-overviews', [EventOverviewController::class, 'index'])->name('api.event-overviews.index');
Route::get('/event-types', [EventTypeController::class, 'index'])->name('api.event-types.index');
Route::get('/external-media', [ExternalMediaController::class, 'index'])->name('api.external-media.index');
Route::get('/testimonials', [TestimonialController::class, 'index'])->name('api.testimonials.index');
Route::get('/google-reviews', [GoogleReviewsController::class, 'show'])->name('api.google-reviews.show');
Route::get('/geo/reverse', [GeoController::class, 'reverse'])
    ->middleware('throttle:geo')
    ->name('api.geo.reverse');
Route::get('/geo/search', [GeoController::class, 'search'])
    ->middleware('throttle:geo')
    ->name('api.geo.search');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:contact')
    ->name('api.contact.store');
Route::post('/newsletter', [NewsletterController::class, 'store'])
    ->middleware('throttle:newsletter')
    ->name('api.newsletter.store');

Route::middleware('auth')->prefix('staff-content')->group(function (): void {
    Route::post('/gallery-items', [\App\Http\Controllers\StaffContentController::class, 'storeGalleryItem']);
    Route::put('/gallery-items/{galleryItem}', [\App\Http\Controllers\StaffContentController::class, 'updateGalleryItem']);
    Route::delete('/gallery-items/{galleryItem}', [\App\Http\Controllers\StaffContentController::class, 'destroyGalleryItem']);
    Route::post('/gallery-items/{galleryItem}/approve', [\App\Http\Controllers\StaffContentController::class, 'approveGalleryItem']);
    Route::post('/gallery-items/{galleryItem}/reject', [\App\Http\Controllers\StaffContentController::class, 'rejectGalleryItem']);
    Route::post('/external-media', [\App\Http\Controllers\StaffContentController::class, 'storeExternalMedia']);
    Route::delete('/external-media/{externalMedia}', [\App\Http\Controllers\StaffContentController::class, 'destroyExternalMedia']);
    Route::post('/{type}/{id}/approve', [\App\Http\Controllers\StaffContentController::class, 'approve'])->whereNumber('id');
    Route::post('/{type}/{id}/reject', [\App\Http\Controllers\StaffContentController::class, 'reject'])->whereNumber('id');
});

/*
|--------------------------------------------------------------------------
| Customer SPA authentication (Sanctum stateful cookies)
| Isolated from Filament Admin / Spatie RBAC.
|--------------------------------------------------------------------------
*/
Route::prefix('customer')->group(function (): void {
    Route::get('/oauth/providers', [CustomerAuthController::class, 'oauthProviders'])
        ->name('api.customer.oauth.providers');

    Route::post('/register', [CustomerAuthController::class, 'register'])
        ->middleware('throttle:customer-register')
        ->name('api.customer.register');
    Route::post('/login', [CustomerAuthController::class, 'login'])
        ->middleware('throttle:customer-login')
        ->name('api.customer.login');
    Route::post('/forgot-password', [CustomerAuthController::class, 'forgotPassword'])
        ->middleware('throttle:customer-forgot-password')
        ->name('api.customer.forgot-password');
    Route::post('/reset-password', [CustomerAuthController::class, 'resetPassword'])
        ->middleware('throttle:customer-forgot-password')
        ->name('api.customer.reset-password');
    Route::post('/email/verify', [EmailVerificationController::class, 'verify'])
        ->middleware('throttle:customer-email-verify')
        ->name('api.customer.email.verify');
    Route::post('/email/resend', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:customer-email-verify')
        ->name('api.customer.email.resend');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verifyLink'])
        ->middleware(['signed', 'throttle:customer-email-verify'])
        ->whereNumber('id')
        ->name('api.customer.email.verify-link');

    Route::get('/oauth/{provider}/redirect', [OAuthController::class, 'redirect'])
        ->middleware(['web', 'throttle:customer-oauth'])
        ->name('api.customer.oauth.redirect');
    Route::get('/oauth/{provider}/callback', [OAuthController::class, 'callback'])
        ->middleware(['web', 'throttle:customer-oauth'])
        ->name('api.customer.oauth.callback');

    Route::middleware('auth:customer')->group(function (): void {
        Route::post('/logout', [CustomerAuthController::class, 'logout'])
            ->name('api.customer.logout');
        Route::get('/me', [CustomerAuthController::class, 'me'])
            ->name('api.customer.me');
        Route::post('/reauthenticate', [AccountSecurityController::class, 'confirmPassword'])
            ->middleware('throttle:customer-login')
            ->name('api.customer.reauthenticate');
        Route::post('/change-password', [AccountSecurityController::class, 'changePassword'])
            ->name('api.customer.change-password');
        Route::post('/change-mobile', [AccountSecurityController::class, 'changeMobile'])
            ->name('api.customer.change-mobile');
        Route::post('/change-email', [AccountSecurityController::class, 'changeEmail'])
            ->name('api.customer.change-email');
        Route::post('/verify-email', [AccountSecurityController::class, 'verifyEmail'])
            ->name('api.customer.verify-email');

        Route::get('/social-accounts', [SocialAccountController::class, 'index'])
            ->name('api.customer.social-accounts.index');
        Route::delete('/social-accounts/{provider}', [SocialAccountController::class, 'destroy'])
            ->name('api.customer.social-accounts.destroy');

        Route::get('/sessions', [SessionController::class, 'index'])
            ->name('api.customer.sessions.index');
        Route::delete('/sessions/others', [SessionController::class, 'destroyOthers'])
            ->name('api.customer.sessions.destroy-others');
        Route::delete('/sessions/{id}', [SessionController::class, 'destroy'])
            ->name('api.customer.sessions.destroy');
    });
});

Route::get('/gallery/items/{id}/download', GalleryDownloadController::class)
    ->middleware(['auth:customer', 'customer.verified', 'throttle:api'])
    ->whereNumber('id')
    ->name('api.gallery.download');

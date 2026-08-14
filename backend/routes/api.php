<?php

use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\EventOverviewController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\GalleryController;
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
Route::get('/testimonials', [TestimonialController::class, 'index'])->name('api.testimonials.index');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:contact')
    ->name('api.contact.store');
Route::post('/newsletter', [NewsletterController::class, 'store'])
    ->middleware('throttle:newsletter')
    ->name('api.newsletter.store');

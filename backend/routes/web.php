<?php

use App\Http\Controllers\AdminMediaPreviewController;
use App\Http\Controllers\MediaDisplayController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\ServePublicStorageController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', RobotsController::class)->name('robots');

Route::get('/protected-media/{token}', MediaDisplayController::class)
    ->where('token', '[A-Za-z0-9\-_]+')
    ->name('protected-media.display');

Route::get('/admin/preview/{token}', AdminMediaPreviewController::class)
    ->where('token', '[A-Za-z0-9\-_]+')
    ->name('admin.media-preview');

Route::get('/storage/{path}', ServePublicStorageController::class)
    ->where('path', '.*')
    ->name('storage.gated');

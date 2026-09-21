<?php

namespace App\Services\Maintenance;

use App\Models\BlogPost;
use App\Models\CtaSection;
use App\Models\Customer;
use App\Models\CustomerSocialAccount;
use App\Models\EventOverview;
use App\Models\ExternalMedia;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\HeroSlide;
use App\Models\NavigationItem;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServicePackage;
use App\Models\Setting;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Services\Gallery\GalleryOriginalStorage;
use App\Services\Google\GoogleReviewsService;
use App\Support\ContentCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * One-click admin cleanup. Deletes only expired Livewire temp files and
 * confidently unreferenced leftovers under studio/uploads (or tmp/temp).
 * Never deletes referenced/active media, logos, gallery originals, or DB rows.
 */
final class WebsiteCleanupService
{
    public const TEMP_MAX_AGE_SECONDS = 86400;

    /**
     * @var list<string>
     */
    private const TEMP_DIRECTORIES = [
        'livewire-tmp',
    ];

    /**
     * @var list<string>
     */
    private const ORPHAN_DIRECTORIES = [
        'studio/uploads',
        'tmp',
        'temp',
    ];

    /**
     * Directories that hold live/system media. Files here are never deleted
     * even if they look unreferenced.
     *
     * @var list<string>
     */
    private const PROTECTED_PREFIXES = [
        'settings/',
        'gallery/',
        'services/',
        'service-packages/',
        'service-categories/',
        'blog/',
        'testimonials/',
        'hero-slides/',
        'event-overviews/',
        'events/',
        'external-media/',
        'team/',
        'cta/',
        'navigation/',
        'customers/',
        'favicon',
    ];

    /**
     * @var list<string>
     */
    private const PROTECTED_EXTENSIONS = [
        'php',
        'exe',
        'bat',
        'cmd',
        'sh',
        'env',
        'htaccess',
        'sqlite',
        'sql',
    ];

    /**
     * @var array<class-string<Model>, list<string>>
     */
    private const MEDIA_COLUMNS = [
        Setting::class => ['logo', 'dark_logo', 'footer_logo', 'favicon', 'about_image', 'opengraph_image'],
        GalleryItem::class => ['image', 'thumbnail', 'original_path', 'opengraph_image'],
        Service::class => ['featured_image', 'banner_image', 'gallery_images', 'opengraph_image'],
        ServicePackage::class => ['image'],
        ServiceCategory::class => ['image', 'opengraph_image'],
        BlogPost::class => ['featured_image', 'banner_image', 'thumbnail', 'opengraph_image'],
        Testimonial::class => ['avatar', 'image'],
        HeroSlide::class => ['desktop_image', 'mobile_image'],
        EventOverview::class => ['image'],
        ExternalMedia::class => ['thumbnail'],
        TeamMember::class => ['photo'],
        CtaSection::class => ['background_image'],
        NavigationItem::class => ['image'],
        Customer::class => ['avatar'],
        CustomerSocialAccount::class => ['avatar'],
    ];

    /**
     * @var array<class-string<Model>, list<string>>
     */
    private const HTML_COLUMNS = [
        Setting::class => ['company_description', 'about_vision', 'about_mission', 'about_journey', 'footer_about'],
        Service::class => ['short_description', 'full_description'],
        ServicePackage::class => ['summary', 'description'],
        BlogPost::class => ['excerpt', 'content'],
        Faq::class => ['answer'],
        Testimonial::class => ['quote', 'body'],
        EventOverview::class => ['description', 'caption'],
        CtaSection::class => ['body', 'subtitle'],
        GalleryItem::class => ['description', 'caption'],
    ];

    public function run(): WebsiteCleanupResult
    {
        $result = new WebsiteCleanupResult;

        try {
            $references = $this->collectReferencedPaths();
            $this->cleanExpiredTemporaryFiles($result, $references);
            $this->cleanConfirmedOrphans($result, $references);
            $result->cacheCleared = $this->clearSafeCaches();
        } catch (Throwable $e) {
            report($e);
            Log::error('Website cleanup failed', ['exception' => $e->getMessage()]);
            $result->failed = true;
            $result->error = $e->getMessage();
        }

        return $result;
    }

    /**
     * @param  array<string, true>  $references
     */
    private function cleanExpiredTemporaryFiles(WebsiteCleanupResult $result, array $references): void
    {
        $cutoff = time() - self::TEMP_MAX_AGE_SECONDS;

        foreach (['local', 'public'] as $disk) {
            foreach (self::TEMP_DIRECTORIES as $directory) {
                $this->scanAndMaybeDelete($result, $disk, $directory, $references, temporary: true, olderThan: $cutoff);
            }
        }
    }

    /**
     * @param  array<string, true>  $references
     */
    private function cleanConfirmedOrphans(WebsiteCleanupResult $result, array $references): void
    {
        foreach (self::ORPHAN_DIRECTORIES as $directory) {
            $this->scanAndMaybeDelete($result, 'public', $directory, $references, temporary: false, olderThan: null);
        }
    }

    /**
     * @param  array<string, true>  $references
     */
    private function scanAndMaybeDelete(
        WebsiteCleanupResult $result,
        string $disk,
        string $directory,
        array $references,
        bool $temporary,
        ?int $olderThan,
    ): void {
        try {
            if (! Storage::disk($disk)->exists($directory)) {
                return;
            }

            $files = Storage::disk($disk)->allFiles($directory);
        } catch (Throwable $e) {
            Log::warning('Website cleanup skipped directory', [
                'disk' => $disk,
                'directory' => $directory,
                'reason' => $e->getMessage(),
            ]);

            return;
        }

        foreach ($files as $path) {
            $result->filesScanned++;

            try {
                if (! $this->canSafelyDelete($disk, $path, $references, $temporary, $olderThan)) {
                    continue;
                }

                $size = 0;
                try {
                    $size = (int) Storage::disk($disk)->size($path);
                } catch (Throwable) {
                    $size = 0;
                }

                if (! $this->canSafelyDelete($disk, $path, $this->collectReferencedPaths(), $temporary, $olderThan)) {
                    Log::info('Website cleanup skipped file after final reference check', [
                        'disk' => $disk,
                        'path' => $path,
                    ]);

                    continue;
                }

                if (! Storage::disk($disk)->delete($path)) {
                    Log::warning('Website cleanup could not delete file', [
                        'disk' => $disk,
                        'path' => $path,
                    ]);

                    continue;
                }

                $result->filesRemoved++;
                $result->bytesFreed += max(0, $size);

                if ($temporary) {
                    $result->temporaryRemoved++;
                } else {
                    $result->orphansRemoved++;
                }
            } catch (Throwable $e) {
                Log::warning('Website cleanup skipped file', [
                    'disk' => $disk,
                    'path' => $path,
                    'reason' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * @param  array<string, true>  $references
     */
    private function canSafelyDelete(
        string $disk,
        string $path,
        array $references,
        bool $temporary,
        ?int $olderThan,
    ): bool {
        $normalized = $this->normalizePath($path);

        if ($normalized === null) {
            return false;
        }

        if ($this->isProtectedPath($normalized)) {
            Log::info('Website cleanup skipped protected path', ['path' => $normalized]);

            return false;
        }

        $allowedRoot = $temporary ? self::TEMP_DIRECTORIES : self::ORPHAN_DIRECTORIES;
        if (! $this->isUnderAllowedDirectory($normalized, $allowedRoot)) {
            Log::info('Website cleanup skipped unknown path', ['path' => $normalized]);

            return false;
        }

        $extension = strtolower(pathinfo($normalized, PATHINFO_EXTENSION));
        if (in_array($extension, self::PROTECTED_EXTENSIONS, true)) {
            return false;
        }

        if ($olderThan !== null) {
            try {
                $modified = Storage::disk($disk)->lastModified($path);
            } catch (Throwable $e) {
                Log::warning('Website cleanup skipped file with unreadable mtime', [
                    'path' => $path,
                    'reason' => $e->getMessage(),
                ]);

                return false;
            }

            if ($modified >= $olderThan) {
                return false;
            }
        }

        if ($this->isReferenced($normalized, $references)) {
            return false;
        }

        $inDatabase = $this->pathAppearsInDatabase($normalized);
        if ($inDatabase !== false) {
            if ($inDatabase === null) {
                Log::warning('Website cleanup skipped file; database reference check was uncertain', [
                    'path' => $normalized,
                ]);
            }

            return false;
        }

        return true;
    }

    /**
     * @param  list<string>  $directories
     */
    private function isUnderAllowedDirectory(string $path, array $directories): bool
    {
        foreach ($directories as $directory) {
            $prefix = rtrim($directory, '/').'/';
            if ($path === $directory || str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function isProtectedPath(string $path): bool
    {
        $lower = strtolower($path);

        if (str_contains($lower, GalleryOriginalStorage::PRIVATE_DIRECTORY)) {
            return true;
        }

        foreach (self::PROTECTED_PREFIXES as $prefix) {
            if (str_starts_with($lower, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, true>  $references
     */
    private function isReferenced(string $path, array $references): bool
    {
        if (isset($references[$path])) {
            return true;
        }

        $basename = basename($path);
        if ($basename !== '' && isset($references[$basename]) && strlen($basename) >= 8) {
            return true;
        }

        foreach (array_keys($references) as $reference) {
            if ($reference !== '' && (str_ends_with($reference, '/'.$path) || str_ends_with($path, '/'.$reference) || $reference === $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, true>
     */
    private function collectReferencedPaths(): array
    {
        $references = [];

        foreach (self::MEDIA_COLUMNS as $model => $columns) {
            $this->collectFromModel($references, $model, $columns, extractHtml: false);
        }

        foreach (self::HTML_COLUMNS as $model => $columns) {
            $this->collectFromModel($references, $model, $columns, extractHtml: true);
        }

        return $references;
    }

    /**
     * @param  array<string, true>  $references
     * @param  class-string<Model>  $model
     * @param  list<string>  $columns
     */
    private function collectFromModel(array &$references, string $model, array $columns, bool $extractHtml): void
    {
        try {
            $query = $model::query();
            if ($this->modelUsesSoftDeletes($model)) {
                $query->withTrashed();
            }

            $query->select(array_values(array_unique(array_merge(['id'], $columns))))
                ->orderBy('id')
                ->chunkById(100, function ($rows) use (&$references, $columns, $extractHtml): void {
                    foreach ($rows as $row) {
                        foreach ($columns as $column) {
                            $this->ingestValue($references, $row->getAttribute($column), $extractHtml);
                        }
                    }
                });
        } catch (Throwable $e) {
            Log::warning('Website cleanup could not read media references', [
                'model' => $model,
                'reason' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param  class-string<Model>  $model
     */
    private function modelUsesSoftDeletes(string $model): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($model), true);
    }

    /**
     * @param  array<string, true>  $references
     */
    private function ingestValue(array &$references, mixed $value, bool $extractHtml): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (is_array($value)) {
            foreach ($value as $item) {
                $this->ingestValue($references, $item, $extractHtml);
            }

            return;
        }

        if (! is_string($value) && ! is_numeric($value)) {
            return;
        }

        $string = (string) $value;
        $normalized = $this->normalizePath($string);
        if ($normalized !== null) {
            $references[$normalized] = true;
        }

        if ($extractHtml) {
            if (preg_match_all('#(?:/storage/|storage/)([^\s"\'>]+)#i', $string, $matches) > 0) {
                foreach ($matches[1] as $match) {
                    $fromHtml = $this->normalizePath($match);
                    if ($fromHtml !== null) {
                        $references[$fromHtml] = true;
                    }
                }
            }
        }
    }

    private function normalizePath(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim(str_replace('\\', '/', $value));
        if ($value === '') {
            return null;
        }

        $value = strtok($value, '?') ?: $value;

        if (preg_match('#(?:^|/)storage/(.+)$#i', $value, $matches) === 1) {
            $value = $matches[1];
        }

        $value = ltrim($value, '/');

        if ($value === '' || str_contains($value, '..')) {
            return null;
        }

        if (str_contains($value, '://') && ! str_contains($value, '/storage/')) {
            return null;
        }

        return $value;
    }

    private function pathAppearsInDatabase(string $relativePath): ?bool
    {
        try {
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $relativePath);
            $needle = '%'.$escaped.'%';

            $sources = [
                ['settings', ['logo', 'dark_logo', 'footer_logo', 'favicon', 'about_image', 'opengraph_image', 'company_description', 'footer_about']],
                ['gallery_items', ['image', 'thumbnail', 'original_path', 'opengraph_image', 'description', 'caption']],
                ['services', ['featured_image', 'banner_image', 'gallery_images', 'opengraph_image', 'short_description', 'full_description']],
                ['service_packages', ['image', 'summary', 'description']],
                ['service_categories', ['image', 'opengraph_image']],
                ['blog_posts', ['featured_image', 'banner_image', 'thumbnail', 'opengraph_image', 'excerpt', 'content']],
                ['testimonials', ['avatar', 'image', 'quote', 'body']],
                ['hero_slides', ['desktop_image', 'mobile_image']],
                ['event_overviews', ['image', 'description', 'caption']],
                ['external_media', ['thumbnail']],
                ['team_members', ['photo']],
                ['cta_sections', ['background_image', 'body']],
                ['navigation_items', ['image']],
                ['customers', ['avatar']],
                ['customer_social_accounts', ['avatar']],
                ['faqs', ['answer']],
            ];

            foreach ($sources as [$table, $columns]) {
                if (! Schema::hasTable($table)) {
                    continue;
                }

                $query = DB::table($table);
                $query->where(function ($builder) use ($columns, $needle): void {
                    foreach ($columns as $column) {
                        $builder->orWhere($column, 'like', $needle);
                    }
                });

                if ($query->exists()) {
                    return true;
                }
            }

            return false;
        } catch (Throwable $e) {
            Log::warning('Website cleanup database reference check failed', [
                'path' => $relativePath,
                'reason' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function clearSafeCaches(): bool
    {
        ContentCache::flushAll();
        Cache::forget(GoogleReviewsService::CACHE_KEY);
        Cache::forget(GoogleReviewsService::STATUS_KEY);

        try {
            Artisan::call('view:clear');
        } catch (Throwable $e) {
            Log::warning('Website cleanup could not clear compiled views', [
                'reason' => $e->getMessage(),
            ]);
        }

        return true;
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Faq;
use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Models\Service;
use App\Services\Seo\SeoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeoController extends Controller
{
    public function __construct(private SeoService $seo) {}

    /**
     * Global website SEO defaults + Organization/LocalBusiness/WebSite schema.
     */
    public function show(): JsonResponse
    {
        return response()->json([
            'data' => $this->seo->forWebsite(),
        ]);
    }

    /**
     * Resolve SEO payload for a page type.
     */
    public function resolve(Request $request): JsonResponse
    {
        $type = (string) $request->query('type', 'website');
        $slug = (string) $request->query('slug', '');
        $path = (string) $request->query('path', '/');
        $title = (string) $request->query('title', 'Page');

        $payload = match ($type) {
            'home' => $this->seo->forHome(),
            'website' => $this->seo->forWebsite(),
            'about' => $this->seo->forAbout(),
            'services' => $this->seo->forServicesIndex(),
            'service' => $this->seo->forService(
                Service::query()->active()->where('slug', $slug)->firstOrFail()
            ),
            'packages' => $this->seo->forPackages(),
            // Without slug: gallery index. With slug: legacy gallery-item resolve.
            'gallery' => filled($slug)
                ? $this->seo->forGalleryItem(
                    GalleryItem::query()->active()->where('slug', $slug)->firstOrFail()
                )
                : $this->seo->forGalleryIndex(),
            'gallery-category' => $this->seo->forGalleryCategory(
                GalleryCategory::query()
                    ->active()
                    ->where('slug', $slug)
                    ->with(['items' => fn ($query) => $query->active()->ordered()->limit(1)])
                    ->firstOrFail()
            ),
            'gallery-item' => $this->seo->forGalleryItem(
                GalleryItem::query()->active()->where('slug', $slug)->firstOrFail()
            ),
            'blog' => filled($slug)
                ? $this->seo->forBlogPost(
                    BlogPost::query()->published()->where('slug', $slug)->with('category')->firstOrFail()
                )
                : $this->seo->forBlogIndex(),
            'blog-index' => $this->seo->forBlogIndex(),
            'faq' => filled($slug)
                ? $this->seo->forFaqDetail(
                    Faq::query()->active()->where('slug', $slug)->firstOrFail()
                )
                : $this->seo->forFaqPage(
                    Faq::query()->active()->ordered()->get()
                ),
            'faq-detail' => $this->seo->forFaqDetail(
                Faq::query()->active()->where('slug', $slug)->firstOrFail()
            ),
            'contact' => $this->seo->forContact(),
            'static' => $this->seo->forStaticPage($path, $title, $request->query('description')),
            default => abort(422, 'Unsupported SEO type.'),
        };

        return response()->json([
            'data' => $payload,
        ]);
    }
}

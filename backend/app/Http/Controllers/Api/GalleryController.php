<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\GalleryCategoryResource;
use App\Http\Resources\Api\GalleryItemResource;
use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Support\ContentCache;
use Illuminate\Http\JsonResponse;

class GalleryController extends Controller
{
    public function index(): JsonResponse
    {
        $payload = ContentCache::remember(ContentCache::GALLERY, function () {
            $items = GalleryItem::query()
                ->with('category:id,name,slug')
                ->active()
                ->withPublicPreview()
                ->ordered()
                ->select([
                    'id',
                    'gallery_category_id',
                    'title',
                    'slug',
                    'media_type',
                    'image',
                    'original_path',
                    'thumbnail',
                    'alt_text',
                    'caption',
                    'youtube_url',
                    'vimeo_url',
                    'video_source',
                    'video_url',
                    'featured',
                    'homepage_featured',
                    'sort_order',
                    'status',
                ])
                ->get();

            return GalleryItemResource::collection($items)->response()->getData(true);
        });

        return response()->json($payload);
    }

    public function categories(): JsonResponse
    {
        $payload = ContentCache::remember(ContentCache::GALLERY_CATEGORIES, function () {
            $categories = GalleryCategory::query()
                ->active()
                ->ordered()
                ->withCount(['items' => fn ($query) => $query->active()->withPublicPreview()])
                ->with(['items' => function ($query): void {
                    $query->active()
                        ->withPublicPreview()
                        ->ordered()
                        ->select([
                            'id',
                            'gallery_category_id',
                            'image',
                            'thumbnail',
                            'media_type',
                            'sort_order',
                            'status',
                        ])
                        ->limit(1);
                }])
                ->get();

            return GalleryCategoryResource::collection($categories)->response()->getData(true);
        });

        return response()->json($payload);
    }

    public function category(string $slug): JsonResponse
    {
        $payload = ContentCache::rememberShow('gallery-category', $slug, function () use ($slug) {
            $category = GalleryCategory::query()
                ->active()
                ->where('slug', $slug)
                ->firstOrFail();

            $items = GalleryItem::query()
                ->with('category:id,name,slug')
                ->active()
                ->withPublicPreview()
                ->where('gallery_category_id', $category->id)
                ->ordered()
                ->select([
                    'id',
                    'gallery_category_id',
                    'title',
                    'slug',
                    'media_type',
                    'image',
                    'original_path',
                    'thumbnail',
                    'alt_text',
                    'caption',
                    'youtube_url',
                    'vimeo_url',
                    'video_source',
                    'video_url',
                    'featured',
                    'homepage_featured',
                    'sort_order',
                    'status',
                ])
                ->get();

            return [
                'data' => [
                    'category' => [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'description' => $category->description,
                    ],
                    'items' => GalleryItemResource::collection($items)->resolve(),
                ],
            ];
        });

        return response()->json($payload);
    }

    public function show(string $slug): JsonResponse
    {
        $payload = ContentCache::rememberShow('gallery', $slug, function () use ($slug) {
            $item = GalleryItem::query()
                ->with('category:id,name,slug')
                ->active()
                ->withPublicPreview()
                ->where('slug', $slug)
                ->firstOrFail();

            return (new GalleryItemResource($item))->response()->getData(true);
        });

        return response()->json($payload);
    }
}

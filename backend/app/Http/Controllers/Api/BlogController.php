<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BlogPostResource;
use App\Models\BlogPost;
use App\Support\ContentCache;
use Illuminate\Http\JsonResponse;

class BlogController extends Controller
{
    public function index(): JsonResponse
    {
        $payload = ContentCache::remember(ContentCache::BLOG, function () {
            $posts = BlogPost::query()
                ->with('category:id,name,slug')
                ->published()
                ->ordered()
                ->select([
                    'id',
                    'blog_category_id',
                    'title',
                    'slug',
                    'excerpt',
                    'featured_image',
                    'thumbnail',
                    'alt_text',
                    'featured',
                    'homepage_featured',
                    'published_at',
                    'reading_time',
                    'author',
                    'tags',
                    'status',
                ])
                ->get();

            return BlogPostResource::collection($posts)->response()->getData(true);
        });

        return response()->json($payload);
    }

    public function show(string $slug): JsonResponse
    {
        $payload = ContentCache::rememberShow('blog', $slug, function () use ($slug) {
            $post = BlogPost::query()
                ->with('category:id,name,slug')
                ->published()
                ->where('slug', $slug)
                ->firstOrFail();

            return (new BlogPostResource($post))->response()->getData(true);
        });

        return response()->json($payload);
    }
}

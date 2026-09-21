<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ServiceResource;
use App\Models\Service;
use App\Support\ContentCache;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    public function index(): JsonResponse
    {
        $payload = ContentCache::remember(ContentCache::SERVICES, function () {
            $services = Service::query()
                ->active()
                ->ordered()
                ->select([
                    'id',
                    'name',
                    'slug',
                    'short_description',
                    'featured_image',
                    'icon',
                    'featured',
                    'show_on_homepage',
                    'sort_order',
                    'status',
                ])
                ->get();

            return ServiceResource::collection($services)->response()->getData(true);
        });

        return response()->json($payload);
    }

    public function show(string $slug): JsonResponse
    {
        $payload = ContentCache::rememberShow('services', $slug, function () use ($slug) {
            $service = Service::query()
                ->active()
                ->where('slug', $slug)
                ->with(['galleryItems' => function ($query): void {
                    $query->active()
                        ->withPublicPreview()
                        ->ordered()
                        ->with('category:id,name,slug')
                        ->limit(24);
                }])
                ->firstOrFail();

            return (new ServiceResource($service))->response()->getData(true);
        });

        return response()->json($payload);
    }
}

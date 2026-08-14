<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\TestimonialResource;
use App\Models\Testimonial;
use App\Support\ContentCache;
use Illuminate\Http\JsonResponse;

class TestimonialController extends Controller
{
    public function index(): JsonResponse
    {
        $payload = ContentCache::remember(ContentCache::TESTIMONIALS, function () {
            $items = Testimonial::query()
                ->active()
                ->ordered()
                ->select([
                    'id',
                    'type',
                    'name',
                    'quote',
                    'body',
                    'avatar',
                    'image',
                    'rating',
                    'video_url',
                    'featured',
                    'homepage_featured',
                    'sort_order',
                    'status',
                ])
                ->get();

            return TestimonialResource::collection($items)->response()->getData(true);
        });

        return response()->json($payload);
    }
}
